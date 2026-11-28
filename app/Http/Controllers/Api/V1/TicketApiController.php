<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TicketApiController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    #[OA\Get(
        path: '/tickets',
        summary: 'Публічний список квитків без персональних даних гостей',
        tags: ['Tickets'],
        responses: [new OA\Response(response: 200, description: 'Список квитків')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return TicketResource::collection(
            $this->ticketService->getPaginatedList($request->user(), forPublicApi: true),
        );
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
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->createTicket($request->validated(), $request->user());

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
    public function update(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        return new TicketResource(
            $this->ticketService->updateTicket($ticket, $request->validated()),
        );
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
    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);
        $this->ticketService->deleteTicket($ticket);

        return response()->json(['message' => 'Квиток успішно видалено.']);
    }
}
