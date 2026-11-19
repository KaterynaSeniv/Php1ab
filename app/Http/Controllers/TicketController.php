<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private array $tickets = [
        [
            'id' => 1,
            'passenger_full_name' => 'Сенів Катерина Дмитрівна',
            'car_number' => 5,
            'seat_number' => 14,
            'price' => 450.50,
            'status' => 'booked',
        ],
        [
            'id' => 2,
            'passenger_full_name' => 'Коваленко Олександр Ігорович',
            'car_number' => 3,
            'seat_number' => 22,
            'price' => 620.00,
            'status' => 'paid',
        ],
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->tickets,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $newTicket = [
            'id' => count($this->tickets) + 1,
            'passenger_full_name' => $validated['passenger_full_name'],
            'car_number' => (int) $validated['car_number'],
            'seat_number' => (int) $validated['seat_number'],
            'price' => (float) $validated['price'],
            'status' => 'booked',
        ];

        $this->tickets[] = $newTicket;

        return response()->json([
            'status' => 'created',
            'data' => $newTicket,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $ticketId = (int) $id;

        foreach ($this->tickets as $ticket) {
            if ($ticket['id'] === $ticketId) {
                return response()->json([
                    'status' => 'success',
                    'data' => $ticket,
                ], 200);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Квиток із зазначеним ідентифікатором не знайдено.',
        ], 404);
    }
}
