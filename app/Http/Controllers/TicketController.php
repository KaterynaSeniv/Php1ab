<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = Ticket::query()
            ->when(auth()->user()->role === 'passenger', fn ($query) => $query->where('user_id', auth()->id()))
            ->with([
                'trainRoute:id,train_number,departure_station,arrival_station,departure_time',
                'services:id,name,price',
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        $routes = TrainRoute::orderBy('train_number')->get();
        $services = Service::orderBy('name')->get();

        // Отримуємо вже заброньовані місця для кожного рейсу
        $bookedSeats = [];
        foreach ($routes as $route) {
            $tickets = Ticket::where('train_route_id', $route->id)->get();
            $bookedSeats[$route->id] = $tickets->map(function ($ticket) {
                return [
                    'car' => $ticket->car_number,
                    'seat' => $ticket->seat_number,
                ];
            })->toArray();
        }

        return view('tickets.create', compact('routes', 'services', 'bookedSeats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'train_route_id' => ['required', 'integer', 'exists:train_routes,id'],
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ], [
            'train_route_id.required' => 'Оберіть рейс поїзда.',
            'train_route_id.exists' => 'Обраний рейс не знайдено.',
            'passenger_full_name.required' => 'Поле "ПІБ пасажира" обов\'язкове.',
            'passenger_full_name.min' => 'ПІБ має містити мінімум 3 символи.',
            'car_number.required' => 'Оберіть місце на схемі.',
            'seat_number.required' => 'Оберіть місце на схемі.',
        ]);

        $route = TrainRoute::findOrFail($validated['train_route_id']);
        $seatError = $this->seatSelectionError($route, (int) $validated['car_number'], (int) $validated['seat_number']);

        if ($seatError !== null) {
            return back()->withInput()->withErrors($seatError);
        }

        $basePrice = (float) $route->base_price;

        // Підраховуємо вартість послуг
        $servicesPrice = 0;
        if (! empty($validated['services'])) {
            $services = Service::whereIn('id', $validated['services'])->get();
            $servicesPrice = $services->sum('price');
        }

        $totalPrice = $basePrice + $servicesPrice;

        $ticket = Ticket::create([
            'qr_token' => Str::random(64),
            'user_id' => $request->user()->id,
            'train_route_id' => (int) $validated['train_route_id'],
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => $totalPrice,
            'status' => 'booked', // Автоматично заброньовано
        ]);

        $ticket->services()->sync($validated['services'] ?? []);

        return redirect()
            ->route('tickets.show', $ticket->id)
            ->with('success', 'Квиток успішно заброньовано! Перейдіть до оплати.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['trainRoute', 'services']);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        $routes = TrainRoute::orderBy('train_number')->get();
        $services = Service::orderBy('name')->get();
        $ticket->load('services');

        // Отримуємо вже заброньовані місця для кожного рейсу
        $bookedSeats = [];
        foreach ($routes as $route) {
            $tickets = Ticket::where('train_route_id', $route->id)
                ->where('id', '!=', $ticket->id) // Виключаємо поточний квиток
                ->get();
            $bookedSeats[$route->id] = $tickets->map(function ($t) {
                return [
                    'car' => $t->car_number,
                    'seat' => $t->seat_number,
                ];
            })->toArray();
        }

        return view('tickets.edit', compact('ticket', 'routes', 'services', 'bookedSeats'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'train_route_id' => ['required', 'integer', 'exists:train_routes,id'],
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'status' => ['nullable', 'string', 'in:booked,paid'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ], [
            'train_route_id.required' => 'Оберіть рейс поїзда.',
            'train_route_id.exists' => 'Обраний рейс не знайдено.',
            'passenger_full_name.required' => 'Поле "ПІБ пасажира" обов\'язкове.',
            'passenger_full_name.min' => 'ПІБ має містити мінімум 3 символи.',
            'car_number.required' => 'Оберіть місце на схемі.',
            'seat_number.required' => 'Оберіть місце на схемі.',
        ]);

        $route = TrainRoute::findOrFail($validated['train_route_id']);
        $seatError = $this->seatSelectionError(
            $route,
            (int) $validated['car_number'],
            (int) $validated['seat_number'],
            $ticket->id,
        );

        if ($seatError !== null) {
            return back()->withInput()->withErrors($seatError);
        }

        $basePrice = (float) $route->base_price;

        // Підраховуємо вартість послуг
        $servicesPrice = 0;
        if (! empty($validated['services'])) {
            $services = Service::whereIn('id', $validated['services'])->get();
            $servicesPrice = $services->sum('price');
        }

        $totalPrice = $basePrice + $servicesPrice;

        $ticket->update([
            'train_route_id' => (int) $validated['train_route_id'],
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => $totalPrice,
            'status' => $validated['status'] ?? $ticket->status,
        ]);

        $ticket->services()->sync($validated['services'] ?? []);

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Дані квитка #'.$ticket->id.' успішно оновлено!');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Квиток успішно видалено із системи.');
    }

    public function pay(Ticket $ticket): RedirectResponse
    {
        $this->authorize('pay', $ticket);

        if ($ticket->status === 'paid') {
            return redirect()
                ->route('tickets.show', $ticket->id)
                ->with('info', 'Цей квиток вже оплачено.');
        }

        $ticket->update(['status' => 'paid']);

        return redirect()
            ->route('tickets.voucher', $ticket->id)
            ->with('success', 'Квиток #'.$ticket->id.' успішно оплачено!');
    }

    public function voucher(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['trainRoute', 'services']);

        return view('tickets.voucher', compact('ticket'));
    }

    /**
     * @return array<string, string>|null
     */
    private function seatSelectionError(TrainRoute $route, int $carNumber, int $seatNumber, ?int $exceptTicketId = null): ?array
    {
        if ($carNumber > $route->car_count) {
            return ['car_number' => 'У цьому рейсі лише '.$route->car_count.' вагонів.'];
        }

        if ($seatNumber > $route->seats_per_car) {
            return ['seat_number' => 'У вагоні цього рейсу лише '.$route->seats_per_car.' місць.'];
        }

        if ($route->isSeatTaken($carNumber, $seatNumber, $exceptTicketId)) {
            return ['seat_number' => 'Це місце вже зайняте. Оберіть інше.'];
        }

        return null;
    }
}
