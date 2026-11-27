<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConductorController extends Controller
{
    public function dashboard(Request $request): View
    {
        $this->authorizeConductor();

        if ($request->user()->role === 'admin') {
            $assignments = TrainRoute::query()
                ->withCount('tickets')
                ->orderBy('departure_time')
                ->get()
                ->each(function (TrainRoute $route): void {
                    $route->pivot = (object) [
                        'shift_start' => $route->departure_time->copy()->subMinutes(45),
                        'shift_end' => $route->departure_time->copy()->addHours(8),
                        'cars_scope' => 'Усі вагони',
                        'instructions' => 'Адміністративний перегляд рейсу.',
                    ];
                });
        } else {
            $assignments = $request->user()->conductorRoutes()
                ->withCount('tickets')
                ->orderByPivot('shift_start')
                ->get();
        }

        $upcoming = $assignments->filter(
            fn (TrainRoute $route): bool => $route->departure_time >= now()
        );

        $completedToday = $assignments->filter(
            fn (TrainRoute $route): bool => $route->departure_time->isPast()
                && $route->departure_time->isToday()
        );

        return view('conductor.dashboard', compact('assignments', 'upcoming', 'completedToday'));
    }

    public function route(Request $request, TrainRoute $route): View
    {
        $this->authorizeConductor();
        $assignment = $this->assignedRoute($request, $route);

        $search = trim((string) $request->string('search'));
        $boardingStatus = $request->string('boarding_status')->toString();

        $tickets = $route->tickets()
            ->with(['services', 'trainRoute'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('passenger_full_name', 'like', '%'.$search.'%')
                        ->orWhere('car_number', (int) $search)
                        ->orWhere('seat_number', (int) $search);
                });
            })
            ->when(in_array($boardingStatus, ['not_checked', 'checked', 'problem'], true), function ($query) use ($boardingStatus) {
                $query->where('boarding_status', $boardingStatus);
            })
            ->orderBy('car_number')
            ->orderBy('seat_number')
            ->get();

        $statistics = [
            'total' => $route->tickets()->count(),
            'paid' => $route->tickets()->where('status', 'paid')->count(),
            'booked' => $route->tickets()->where('status', 'booked')->count(),
            'checked' => $route->tickets()->where('boarding_status', 'checked')->count(),
            'not_checked' => $route->tickets()->where('boarding_status', 'not_checked')->count(),
            'available' => max(0, ($route->car_count * $route->seats_per_car) - $route->tickets()->count()),
        ];

        return view('conductor.route', [
            'route' => $route,
            'assignment' => $assignment,
            'tickets' => $tickets,
            'statistics' => $statistics,
        ]);
    }

    public function checkIn(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeConductor();
        $this->authorize('checkIn', $ticket);
        $this->assignedRoute($request, $ticket->trainRoute);

        $validated = $request->validate([
            'boarding_status' => ['required', 'in:checked,problem,not_checked'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $ticket->update([
            'boarding_status' => $validated['boarding_status'],
            'checked_at' => $validated['boarding_status'] === 'not_checked' ? null : now(),
            'checked_by' => $validated['boarding_status'] === 'not_checked' ? null : $request->user()->id,
        ]);

        $message = match ($validated['boarding_status']) {
            'checked' => 'Посадку пасажира підтверджено.',
            'problem' => 'Заявку пасажира позначено як проблемну.',
            default => 'Статус перевірки скинуто.',
        };

        return redirect()->back()->with('success', $message);
    }

    public function scan(Request $request, string $token): View
    {
        $this->authorizeConductor();

        $ticket = Ticket::query()
            ->with(['trainRoute', 'services'])
            ->where('qr_token', $token)
            ->firstOrFail();

        $this->ensureCanVerifyTicket($request, $ticket);

        return view('conductor.scan', compact('ticket'));
    }

    public function confirmScan(Request $request, string $token): RedirectResponse
    {
        $this->authorizeConductor();

        $ticket = Ticket::query()
            ->where('qr_token', $token)
            ->firstOrFail();

        $this->ensureCanVerifyTicket($request, $ticket);

        if ($ticket->status !== 'paid') {
            return redirect()->back()->withErrors([
                'ticket' => 'Квиток не оплачене: посадку підтвердити неможливо.',
            ]);
        }

        $ticket->update([
            'boarding_status' => 'checked',
            'checked_at' => now(),
            'checked_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('conductor.scan', $token)
            ->with('success', 'QR-квиток перевірено. Посадку підтверджено.');
    }

    private function ensureCanVerifyTicket(Request $request, Ticket $ticket): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $assigned = $request->user()->conductorRoutes()
            ->whereKey($ticket->train_route_id)
            ->exists();

        abort_unless($assigned, 403, 'Цей квиток належить до рейсу, який вам не призначено.');
    }

    private function authorizeConductor(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['conductor', 'admin'], true), 403);
    }

    private function assignedRoute(Request $request, TrainRoute $route): object
    {
        if ($request->user()->role === 'admin') {
            return (object) [
                'shift_start' => $route->departure_time,
                'shift_end' => $route->departure_time->copy()->addHours(8),
                'cars_scope' => 'Усі вагони',
                'instructions' => 'Адміністративний перегляд рейсу.',
            ];
        }

        $assignment = $request->user()->conductorRoutes()
            ->whereKey($route->id)
            ->first();

        abort_if($assignment === null, 403);

        return (object) [
            'shift_start' => $assignment->pivot->shift_start,
            'shift_end' => $assignment->pivot->shift_end,
            'cars_scope' => $assignment->pivot->cars_scope,
            'instructions' => $assignment->pivot->instructions,
        ];
    }
}
