<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainRoute\StoreTrainRouteRequest;
use App\Http\Requests\TrainRoute\UpdateTrainRouteRequest;
use App\Models\TrainRoute;
use App\Services\TrainRouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteScheduleController extends Controller
{
    public function __construct(private readonly TrainRouteService $trainRouteService) {}

    public function search(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'string', 'max:100'],
            'to' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'from.max' => 'Назва станції відправлення має містити не більше 100 символів.',
            'to.max' => 'Назва станції призначення має містити не більше 100 символів.',
            'date.date_format' => 'Оберіть коректну дату відправлення.',
        ]);

        $query = TrainRoute::query()->withCount('tickets');

        if (! empty($validated['from'])) {
            $query->where('departure_station', 'like', '%'.trim($validated['from']).'%');
        }

        if (! empty($validated['to'])) {
            $query->where('arrival_station', 'like', '%'.trim($validated['to']).'%');
        }

        if (! empty($validated['date'])) {
            $query->whereDate('departure_time', $validated['date']);
        } else {
            $query->where('departure_time', '>=', now());
        }

        $searchResults = $query
            ->orderBy('departure_time')
            ->orderBy('train_number')
            ->get();

        return view('home', [
            'searchResults' => $searchResults,
            'searchPerformed' => true,
        ]);
    }

    public function index(): View
    {
        $routes = $this->trainRouteService->getPaginatedList();

        return view('routes.index', compact('routes'));
    }

    public function create(): View
    {
        $this->authorize('create', TrainRoute::class);

        return view('routes.create');
    }

    public function store(StoreTrainRouteRequest $request): RedirectResponse
    {
        $this->trainRouteService->createRoute($request->validated());

        return redirect()
            ->route('routes.index')
            ->with('success', 'Новий залізничний рейс успішно додано!');
    }

    public function show(TrainRoute $route): View
    {
        $route->load('tickets')->loadCount('tickets');

        return view('routes.show', compact('route'));
    }

    public function edit(TrainRoute $route): View
    {
        $this->authorize('update', $route);

        return view('routes.edit', compact('route'));
    }

    public function update(UpdateTrainRouteRequest $request, TrainRoute $route): RedirectResponse
    {
        $this->trainRouteService->updateRoute($route, $request->validated());

        return redirect()
            ->route('routes.index')
            ->with('success', 'Рейс №'.$route->train_number.' успішно оновлено!');
    }

    public function destroy(TrainRoute $route): RedirectResponse
    {
        $this->authorize('delete', $route);
        $this->trainRouteService->deleteRoute($route);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Рейс успішно видалено зі списку.');
    }
}
