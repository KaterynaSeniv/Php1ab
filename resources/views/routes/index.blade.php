@extends('layouts.app')

@section('title', 'Рейси поїздів')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-train me-2 text-success"></i>Розклад залізничних рейсів
        </h1>
        <p class="text-muted small mb-0">Керування маршрутами та розкладом поїздів</p>
    </div>
    <a href="{{ route('routes.create') }}" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle me-1"></i>Додати рейс
    </a>
</div>

<div class="card shadow-lg border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-muted small">#</th>
                    <th>Номер поїзда</th>
                    <th>Маршрут сполучення</th>
                    <th>Час відправлення</th>
                    <th>Тип вагонів</th>
                    <th>Вагони/Місця</th>
                    <th>Базова вартість</th>
                    <th>Оформлено квитків</th>
                    <th style="width: 200px;">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($routes as $route)
                    <tr>
                        <td class="text-muted small">{{ $route->id }}</td>
                        <td><span class="badge bg-primary fs-6">№{{ $route->train_number }}</span></td>
                        <td class="fw-semibold">{{ $route->departure_station }} — {{ $route->arrival_station }}</td>
                        <td>
                            <i class="bi bi-clock me-1 text-muted"></i>{{ $route->formattedDeparture() }}
                        </td>
                        <td>
                            @if ($route->car_type === 'coupe')
                                <span class="badge bg-primary">Купе</span>
                            @else
                                <span class="badge bg-warning text-dark">Плацкарт</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $route->car_count }} ваг / {{ $route->seats_per_car }} міс</small>
                        </td>
                        <td class="fw-bold text-success">{{ number_format((float) $route->base_price, 2) }} грн</td>
                        <td><span class="badge bg-info"><i class="bi bi-people me-1"></i>{{ $route->tickets_count }} шт.</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('routes.show', $route->id) }}" class="btn btn-outline-info" title="Перегляд">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('routes.edit', $route->id) }}" class="btn btn-outline-warning" title="Редагувати">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('routes.destroy', $route->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Видалити цей рейс разом із квитками?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Видалити">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-train display-4 d-block mb-2"></i>
                            Рейсів поїздів ще не додано.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $routes->links('pagination::bootstrap-5') }}
</div>
@endsection