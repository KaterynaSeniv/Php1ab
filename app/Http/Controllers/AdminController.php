<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $this->authorizeAdmin();

        $statistics = [
            'users' => User::count(),
            'passengers' => User::where('role', 'passenger')->count(),
            'conductors' => User::where('role', 'conductor')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'routes' => TrainRoute::count(),
            'tickets' => Ticket::count(),
            'paid_tickets' => Ticket::where('status', 'paid')->count(),
            'services' => Service::count(),
            'problems' => Ticket::where('boarding_status', 'problem')->count(),
            'revenue' => (float) Ticket::where('status', 'paid')->sum('price'),
        ];

        $users = User::query()
            ->withCount('tickets')
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'conductor' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(10, ['*'], 'users_page');

        $routes = TrainRoute::query()
            ->with(['conductors:id,name,email'])
            ->withCount('tickets')
            ->orderBy('departure_time')
            ->paginate(8, ['*'], 'routes_page');

        $recentTickets = Ticket::query()
            ->with(['trainRoute:id,train_number,departure_station,arrival_station', 'services:id,name'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $conductors = User::where('role', 'conductor')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'statistics' => $statistics,
            'users' => $users,
            'routes' => $routes,
            'recentTickets' => $recentTickets,
            'conductors' => $conductors,
        ]);
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'role' => ['required', Rule::in(['passenger', 'conductor', 'admin'])],
        ]);

        if ($request->user()->is($user) && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Адміністратор не може зняти власну роль.']);
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Роль користувача «'.$user->name.'» оновлено.');
    }

    public function assignConductor(Request $request, TrainRoute $route): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'conductor_id' => ['required', 'integer', 'exists:users,id'],
            'shift_start' => ['required', 'date'],
            'shift_end' => ['required', 'date', 'after:shift_start'],
            'cars_scope' => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $conductor = User::findOrFail($validated['conductor_id']);
        abort_unless($conductor->role === 'conductor', 422);

        $route->conductors()->syncWithoutDetaching([
            $conductor->id => [
                'shift_start' => $validated['shift_start'],
                'shift_end' => $validated['shift_end'],
                'cars_scope' => $validated['cars_scope'] ?: 'Усі вагони',
                'instructions' => $validated['instructions'] ?: null,
            ],
        ]);

        return back()->with('success', 'Провідника «'.$conductor->name.'» призначено на рейс №'.$route->train_number.'.');
    }

    public function removeConductor(Request $request, TrainRoute $route, User $conductor): RedirectResponse
    {
        $this->authorizeAdmin();

        $route->conductors()->detach($conductor->id);

        return back()->with('success', 'Призначення провідника скасовано.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }
}
