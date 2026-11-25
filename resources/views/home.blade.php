@extends('layouts.app')

@section('title', 'Головна — Електронна система продажу залізничних квитків')

@section('content')
<div class="hero-gradient text-white p-5 mb-5 shadow-lg">
    <div class="container py-4 position-relative">
        <div class="position-relative z-1">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-train-front me-2"></i>Електронна система продажу залізничних квитків
            </h1>
            <p class="col-md-10 fs-5 text-white-90 mb-4">
                Сучасний вебзастосунок для обліку залізничних рейсів, оформлення пасажирських квитків та керування додатковими сервісами у поїздах.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('tickets.create') }}" class="btn btn-success btn-lg">
                    <i class="bi bi-plus-circle me-1"></i>Оформити новий квиток
                </a>
                <a href="{{ route('routes.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-geo-alt me-1"></i>Розклад рейсів
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Пошук рейсів -->
<div class="container mb-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-search me-2"></i>Пошук рейсів
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('home.search') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search-from" class="form-label text-muted">Звідки</label>
                    <input id="search-from" type="text" name="from" class="form-control" placeholder="Наприклад: Київ" value="{{ old('from', request('from')) }}">
                </div>
                <div class="col-md-3">
                    <label for="search-to" class="form-label text-muted">Куди</label>
                    <input id="search-to" type="text" name="to" class="form-control" placeholder="Наприклад: Львів" value="{{ old('to', request('to')) }}">
                </div>
                <div class="col-md-3">
                    <label for="search-date" class="form-label text-muted">Дата відправлення</label>
                    <input id="search-date" type="date" name="date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('date', request('date')) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Знайти рейси
                    </button>
                    @if (request()->anyFilled(['from', 'to', 'date']))
                        <a href="{{ route('home') }}" class="btn btn-light" title="Скинути пошук" aria-label="Скинути пошук">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
            @if ($errors->any())
                <div class="alert alert-danger mt-3 mb-0">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Результати пошуку -->
@if (isset($searchPerformed))
<div class="container mb-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">
                <i class="bi bi-check-circle me-2"></i>Знайдені рейси ({{ $searchResults->count() }})
            </h4>
        </div>
        <div class="card-body">
            @if ($searchResults->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Номер поїзда</th>
                            <th>Маршрут</th>
                            <th>Час відправлення</th>
                            <th>Тип</th>
                            <th>Вартість</th>
                            <th>Вільних місць</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($searchResults as $route)
                        @php
                            $totalSeats = $route->car_count * $route->seats_per_car;
                            $availableSeats = max(0, $totalSeats - $route->tickets_count);
                        @endphp
                        <tr>
                            <td><span class="badge bg-primary fs-6">№{{ $route->train_number }}</span></td>
                            <td class="fw-semibold">{{ $route->departure_station }} — {{ $route->arrival_station }}</td>
                            <td>{{ $route->formattedDeparture() }}</td>
                            <td>
                                @if ($route->car_type === 'coupe')
                                    <span class="badge bg-primary">Купе</span>
                                @else
                                    <span class="badge bg-warning text-dark">Плацкарт</span>
                                @endif
                            </td>
                            <td class="fw-bold text-success">{{ number_format((float) $route->base_price, 2) }} грн</td>
                            <td>
                                <span class="badge {{ $availableSeats > 0 ? 'bg-info' : 'bg-danger' }}">{{ $availableSeats }}/{{ $totalSeats }}</span>
                            </td>
                            <td>
                                @if ($availableSeats > 0)
                                    <a href="{{ route('tickets.create') }}?route_id={{ $route->id }}" class="btn btn-success btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i>Забронювати
                                    </a>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>Місць немає</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-muted mb-0 py-4">
                <i class="bi bi-search d-block display-6 mb-2"></i>
                Рейсів за цими параметрами не знайдено.
            </p>
            @endif
        </div>
    </div>
</div>
@endif

@endsection