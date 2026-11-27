<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TrainRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteScheduleController extends Controller
{
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
        $routes = TrainRoute::query()->withCount('tickets')->orderBy('train_number')->paginate(10);

        return view('routes.index', compact('routes'));
    }

    public function create(): View
    {
        $this->authorize('create', TrainRoute::class);

        return view('routes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TrainRoute::class);

        $validated = $request->validate([
            'train_number' => ['required', 'string', 'unique:train_routes,train_number'],
            'departure_station' => ['required', 'string', 'min:2', 'max:100'],
            'arrival_station' => ['required', 'string', 'min:2', 'max:100'],
            'departure_time' => ['required', 'date', 'after:now'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'car_count' => ['required', 'integer', 'min:1', 'max:25'],
            'seats_per_car' => ['required', 'integer', 'min:1', 'max:54'],
            'car_type' => ['required', 'in:coupe,platskart'],
        ], [
            'train_number.required' => 'Поле "Номер поїзда" обов\'язкове для заповнення.',
            'train_number.unique' => 'Поїзд з таким номером вже існує.',
            'departure_station.required' => 'Поле "Станція відправлення" обов\'язкове.',
            'departure_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'arrival_station.required' => 'Поле "Станція прибуття" обов\'язкове.',
            'arrival_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'departure_time.required' => 'Поле "Час відправлення" обов\'язкове.',
            'departure_time.date' => 'Некоректна дата та час відправлення.',
            'departure_time.after' => 'Час відправлення має бути в майбутньому.',
            'base_price.required' => 'Поле "Базова вартість" обов\'язкове.',
            'base_price.numeric' => 'Базова вартість має бути числом.',
            'base_price.min' => 'Базова вартість не може бути від\'ємною.',
            'car_count.required' => 'Поле "Кількість вагонів" обов\'язкове.',
            'car_count.min' => 'Кількість вагонів має бути від 1 до 25.',
            'car_count.max' => 'Кількість вагонів має бути від 1 до 25.',
            'seats_per_car.required' => 'Поле "Місць у вагоні" обов\'язкове.',
            'seats_per_car.min' => 'Кількість місць має бути від 1 до 54.',
            'seats_per_car.max' => 'Кількість місць має бути від 1 до 54.',
            'car_type.required' => 'Оберіть тип вагонів.',
            'car_type.in' => 'Тип вагонів має бути або купе, або плацкарт.',
        ]);

        TrainRoute::create($validated);

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

    public function update(Request $request, TrainRoute $route): RedirectResponse
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'train_number' => ['required', 'string', 'unique:train_routes,train_number,'.$route->id],
            'departure_station' => ['required', 'string', 'min:2', 'max:100'],
            'arrival_station' => ['required', 'string', 'min:2', 'max:100'],
            'departure_time' => ['required', 'date'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'car_count' => ['required', 'integer', 'min:1', 'max:25'],
            'seats_per_car' => ['required', 'integer', 'min:1', 'max:54'],
            'car_type' => ['required', 'in:coupe,platskart'],
        ], [
            'train_number.required' => 'Поле "Номер поїзда" обов\'язкове для заповнення.',
            'train_number.unique' => 'Поїзд з таким номером вже існує.',
            'departure_station.required' => 'Поле "Станція відправлення" обов\'язкове.',
            'departure_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'arrival_station.required' => 'Поле "Станція прибуття" обов\'язкове.',
            'arrival_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'departure_time.required' => 'Поле "Час відправлення" обов\'язкове.',
            'departure_time.date' => 'Некоректна дата та час відправлення.',
            'base_price.required' => 'Поле "Базова вартість" обов\'язкове.',
            'base_price.numeric' => 'Базова вартість має бути числом.',
            'base_price.min' => 'Базова вартість не може бути від\'ємною.',
            'car_count.required' => 'Поле "Кількість вагонів" обов\'язкове.',
            'car_count.min' => 'Кількість вагонів має бути від 1 до 25.',
            'car_count.max' => 'Кількість вагонів має бути від 1 до 25.',
            'seats_per_car.required' => 'Поле "Місць у вагоні" обов\'язкове.',
            'seats_per_car.min' => 'Кількість місць має бути від 1 до 54.',
            'seats_per_car.max' => 'Кількість місць має бути від 1 до 54.',
            'car_type.required' => 'Оберіть тип вагонів.',
            'car_type.in' => 'Тип вагонів має бути або купе, або плацкарт.',
        ]);

        $route->update($validated);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Рейс №'.$route->train_number.' успішно оновлено!');
    }

    public function destroy(TrainRoute $route): RedirectResponse
    {
        $this->authorize('delete', $route);

        $route->delete();

        return redirect()
            ->route('routes.index')
            ->with('success', 'Рейс успішно видалено зі списку.');
    }
}
