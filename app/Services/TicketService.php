<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendTicketCreatedNotificationJob;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function getPaginatedList(?User $viewer, bool $forPublicApi = false): LengthAwarePaginator
    {
        $query = Ticket::query()->with([
            'trainRoute:id,train_number,departure_station,arrival_station,departure_time',
            'services:id,name,price',
        ]);

        if ($viewer !== null && $forPublicApi && ! in_array($viewer->role, ['admin', 'conductor'], true)) {
            $query->whereBelongsTo($viewer);
        } elseif ($viewer !== null && ! $forPublicApi && $viewer->role === 'passenger') {
            $query->whereBelongsTo($viewer);
        }

        return $query->orderByDesc('id')->paginate(10);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTicket(array $data, User $passenger): Ticket
    {
        $ticket = DB::transaction(function () use ($data, $passenger): Ticket {
            $route = TrainRoute::query()
                ->lockForUpdate()
                ->findOrFail((int) $data['train_route_id']);

            $carNumber = (int) $data['car_number'];
            $seatNumber = (int) $data['seat_number'];
            $this->validateSeatSelection($route, $carNumber, $seatNumber);

            $services = Service::query()
                ->whereIn('id', $data['services'] ?? [])
                ->get();

            $ticket = Ticket::create([
                'user_id' => $passenger->id,
                'train_route_id' => $route->id,
                'passenger_full_name' => $data['passenger_full_name'],
                'car_number' => $carNumber,
                'seat_number' => $seatNumber,
                'price' => $this->calculateTotalPrice(
                    (float) $route->base_price,
                    $services->pluck('price')->map(static fn (mixed $price): float => (float) $price)->all(),
                ),
                'status' => 'booked',
            ]);

            $ticket->services()->sync($services->modelKeys());

            return $ticket->load(['trainRoute', 'services']);
        });

        SendTicketCreatedNotificationJob::dispatch($ticket);

        return $ticket;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTicket(Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data): Ticket {
            $route = TrainRoute::query()
                ->lockForUpdate()
                ->findOrFail((int) $data['train_route_id']);

            $carNumber = (int) $data['car_number'];
            $seatNumber = (int) $data['seat_number'];
            $this->validateSeatSelection($route, $carNumber, $seatNumber, $ticket->id);

            $services = Service::query()
                ->whereIn('id', $data['services'] ?? [])
                ->get();

            $ticket->update([
                'train_route_id' => $route->id,
                'passenger_full_name' => $data['passenger_full_name'],
                'car_number' => $carNumber,
                'seat_number' => $seatNumber,
                'price' => $this->calculateTotalPrice(
                    (float) $route->base_price,
                    $services->pluck('price')->map(static fn (mixed $price): float => (float) $price)->all(),
                ),
                'status' => $data['status'] ?? $ticket->status,
            ]);

            $ticket->services()->sync($services->modelKeys());

            return $ticket->load(['trainRoute', 'services']);
        });
    }

    public function deleteTicket(Ticket $ticket): void
    {
        DB::transaction(function () use ($ticket): void {
            $ticket->services()->detach();
            $ticket->delete();
        });
    }

    public function markAsPaid(Ticket $ticket): bool
    {
        return DB::transaction(function () use ($ticket): bool {
            $lockedTicket = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            if ($lockedTicket->status === 'paid') {
                return false;
            }

            $lockedTicket->update(['status' => 'paid']);

            return true;
        });
    }

    /**
     * @param  Collection<int, TrainRoute>  $routes
     * @return array<int, array<int, array{car: int, seat: int}>>
     */
    public function getBookedSeats(Collection $routes, ?Ticket $exceptTicket = null): array
    {
        $routeIds = $routes->modelKeys();
        $bookedSeats = array_fill_keys($routeIds, []);

        $tickets = Ticket::query()
            ->whereIn('train_route_id', $routeIds)
            ->when($exceptTicket, fn ($query) => $query->whereKeyNot($exceptTicket->id))
            ->get(['train_route_id', 'car_number', 'seat_number']);

        foreach ($tickets as $ticket) {
            $bookedSeats[$ticket->train_route_id][] = [
                'car' => $ticket->car_number,
                'seat' => $ticket->seat_number,
            ];
        }

        return $bookedSeats;
    }

    /**
     * @param  array<int, float>  $servicePrices
     */
    public function calculateTotalPrice(float $basePrice, array $servicePrices): float
    {
        return round($basePrice + array_sum($servicePrices), 2);
    }

    private function validateSeatSelection(
        TrainRoute $route,
        int $carNumber,
        int $seatNumber,
        ?int $exceptTicketId = null,
    ): void {
        if ($carNumber > $route->car_count) {
            throw ValidationException::withMessages([
                'car_number' => 'У цьому рейсі лише '.$route->car_count.' вагонів.',
            ]);
        }

        if ($seatNumber > $route->seats_per_car) {
            throw ValidationException::withMessages([
                'seat_number' => 'У вагоні цього рейсу лише '.$route->seats_per_car.' місць.',
            ]);
        }

        if ($route->isSeatTaken($carNumber, $seatNumber, $exceptTicketId)) {
            throw ValidationException::withMessages([
                'seat_number' => 'Це місце вже зайняте. Оберіть інше.',
            ]);
        }
    }
}
