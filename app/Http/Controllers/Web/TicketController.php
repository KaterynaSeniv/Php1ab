<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->ticketService->getPaginatedList(auth()->user());

        return view('tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        $routes = TrainRoute::orderBy('train_number')->get();
        $services = Service::orderBy('name')->get();
        $bookedSeats = $this->ticketService->getBookedSeats($routes);

        return view('tickets.create', compact('routes', 'services', 'bookedSeats'));
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->ticketService->createTicket($request->validated(), $request->user());

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
        $bookedSeats = $this->ticketService->getBookedSeats($routes, $ticket);

        return view('tickets.edit', compact('ticket', 'routes', 'services', 'bookedSeats'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->updateTicket($ticket, $request->validated());

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Дані квитка #'.$ticket->id.' успішно оновлено!');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);
        $this->ticketService->deleteTicket($ticket);

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Квиток успішно видалено із системи.');
    }

    public function pay(Ticket $ticket): RedirectResponse
    {
        $this->authorize('pay', $ticket);

        if (! $this->ticketService->markAsPaid($ticket)) {
            return redirect()
                ->route('tickets.show', $ticket->id)
                ->with('info', 'Цей квиток вже оплачено.');
        }

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
}
