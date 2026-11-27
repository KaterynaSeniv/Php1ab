<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class TicketApiController extends Controller
{
    #[OA\Get(
        path: '/tickets',
        summary: 'Публічний список квитків без персональних даних гостей',
        tags: ['Tickets'],
        responses: [new OA\Response(response: 200, description: 'Список квитків')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Ticket::query()->with(['trainRoute', 'services']);

        if ($request->user() && ! in_array($request->user()->role, ['admin', 'conductor'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return TicketResource::collection($query->orderByDesc('id')->paginate(10));
    }

    #[OA\Get(
        path: '/tickets/{ticket}',
        summary: 'Публічний перегляд квитка з прихованими PII для гостей',
        tags: ['Tickets'],
        parameters: [new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Дані квитка'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
        ]
    )]
    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);
        $ticket->load(['trainRoute', 'services']);

        return new TicketResource($ticket);
    }

    #[OA\Post(
        path: '/tickets',
        summary: 'Створення квитка',
        security: [['bearerAuth' => []]],
        tags: ['Tickets'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['train_route_id', 'passenger_full_name', 'car_number', 'seat_number'],
                properties: [
                    new OA\Property(property: 'train_route_id', type: 'integer', example: 1),
                    new OA\Property(property: 'passenger_full_name', type: 'string', example: 'Іван Петренко'),
                    new OA\Property(property: 'car_number', type: 'integer', example: 3),
                    new OA\Property(property: 'seat_number', type: 'integer', example: 12),
                    new OA\Property(property: 'services', type: 'array', items: new OA\Items(type: 'integer'), example: [1]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Квиток створено'),
            new OA\Response(response: 401, description: 'Токен відсутній'),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'train_route_id' => ['required', 'integer', 'exists:train_routes,id'],
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        $route = TrainRoute::findOrFail($validated['train_route_id']);
        $this->validateSeatSelection($request, $route, (int) $validated['car_number'], (int) $validated['seat_number']);

        $services = Service::whereIn('id', $validated['services'] ?? [])
            ->get();

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'train_route_id' => $route->id,
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => (float) $route->base_price + $services->sum('price'),
            'status' => 'booked',
        ]);

        $ticket->services()->sync($services->modelKeys());
        $ticket->load(['trainRoute', 'services']);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/tickets/{ticket}',
        summary: 'Оновлення квитка',
        security: [['bearerAuth' => []]],
        tags: ['Tickets'],
        parameters: [new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Квиток оновлено'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
        ]
    )]
    public function update(Request $request, Ticket $ticket): TicketResource
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
        ]);

        $route = TrainRoute::findOrFail($validated['train_route_id']);
        $this->validateSeatSelection($request, $route, (int) $validated['car_number'], (int) $validated['seat_number'], $ticket->id);

        $services = Service::whereIn('id', $validated['services'] ?? [])
            ->get();

        $ticket->update([
            'train_route_id' => $route->id,
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => (float) $route->base_price + $services->sum('price'),
            'status' => $validated['status'] ?? $ticket->status,
        ]);

        $ticket->services()->sync($services->modelKeys());
        $ticket->load(['trainRoute', 'services']);

        return new TicketResource($ticket);
    }

    #[OA\Delete(
        path: '/tickets/{ticket}',
        summary: 'Видалення квитка',
        security: [['bearerAuth' => []]],
        tags: ['Tickets'],
        parameters: [new OA\Parameter(name: 'ticket', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Квиток видалено'),
            new OA\Response(response: 403, description: 'Доступ заборонено'),
        ]
    )]
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);
        $ticket->delete();

        return response()->json(['message' => 'Квиток успішно видалено.']);
    }

    private function validateSeatSelection(Request $request, TrainRoute $route, int $carNumber, int $seatNumber, ?int $exceptTicketId = null): void
    {
        if ($carNumber > $route->car_count) {
            throw ValidationException::withMessages([
                'car_number' => 'У цьому рейсі менше вагонів.',
            ]);
        }

        if ($seatNumber > $route->seats_per_car) {
            throw ValidationException::withMessages([
                'seat_number' => 'У вагоні менше місць.',
            ]);
        }

        if ($route->isSeatTaken($carNumber, $seatNumber, $exceptTicketId)) {
            throw ValidationException::withMessages([
                'seat_number' => 'Це місце вже зайняте.',
            ]);
        }
    }
}
