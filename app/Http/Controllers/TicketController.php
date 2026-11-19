<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(): JsonResponse
    {
        $tickets = Ticket::query()
            ->with([
                'trainRoute:id,train_number,departure_station,arrival_station,departure_time',
                'services:id,name,price',
            ])
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'train_route_id' => ['required', 'integer', 'exists:train_routes,id'],
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:booked,paid'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        $ticket = Ticket::create([
            'train_route_id' => (int) $validated['train_route_id'],
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => (float) $validated['price'],
            'status' => $validated['status'] ?? 'booked',
        ]);

        if (! empty($validated['services'])) {
            $ticket->services()->sync($validated['services']);
        }

        $ticket->load([
            'trainRoute:id,train_number,departure_station,arrival_station',
            'services:id,name,price',
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $ticket,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $ticket = Ticket::with(['trainRoute', 'services'])->find((int) $id);

        if (! $ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Квиток із зазначеним ідентифікатором не знайдено.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $ticket,
        ], 200);
    }
}
