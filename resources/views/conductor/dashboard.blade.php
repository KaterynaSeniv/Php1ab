@extends('layouts.app')

@section('title', 'Кабінет провідника')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="text-uppercase text-primary fw-bold small mb-1">Особистий кабінет провідника</p>
        <h1 class="h3 mb-1">Розклад призначених рейсів</h1>
        <p class="text-muted mb-0">{{ auth()->user()->name }} · змінено: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
        <i class="bi bi-calendar-check me-1"></i>{{ $assignments->count() }} призначення
    </span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Найближчі рейси</div>
                <div class="display-6 fw-bold text-primary">{{ $upcoming->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Сьогодні завершуються</div>
                <div class="display-6 fw-bold text-success">{{ $completedToday->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Всього пасажирів на рейсах</div>
                <div class="display-6 fw-bold text-warning">{{ $assignments->sum('tickets_count') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse ($assignments as $route)
        @php
            $shiftStart = \Carbon\Carbon::parse($route->pivot->shift_start);
            $shiftEnd = \Carbon\Carbon::parse($route->pivot->shift_end);
        @endphp
        <div class="col-lg-6">
            <article class="card border-0 shadow-lg h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary fs-6">№{{ $route->train_number }}</span>
                        <span class="badge {{ $route->departure_time->isPast() ? 'bg-secondary' : 'bg-success' }} ms-2">
                            {{ $route->departure_time->isPast() ? 'Завершено/минуло' : 'Майбутній рейс' }}
                        </span>
                    </div>
                    <i class="bi bi-train-front fs-4 text-primary"></i>
                </div>
                <div class="card-body">
                    <h2 class="h5">{{ $route->departure_station }} <i class="bi bi-arrow-right mx-1"></i> {{ $route->arrival_station }}</h2>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <div class="small text-muted">Відправлення</div>
                            <div class="fw-semibold"><i class="bi bi-clock me-1"></i>{{ $route->formattedDeparture() }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Мої вагони</div>
                            <div class="fw-semibold"><i class="bi bi-grid-3x3-gap me-1"></i>{{ $route->pivot->cars_scope }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Початок зміни</div>
                            <div class="fw-semibold">{{ $shiftStart->format('d.m.Y H:i') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Завершення зміни</div>
                            <div class="fw-semibold">{{ $shiftEnd->format('d.m.Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-3">
                        <div class="small text-muted mb-1">Інструкція на рейс</div>
                        <div>{{ $route->pivot->instructions ?: 'Додаткових інструкцій немає.' }}</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-ticket-perforated me-1"></i>{{ $route->tickets_count }} квитків
                        </span>
                        <a href="{{ route('conductor.route', $route) }}" class="btn btn-primary">
                            Пасажирський список <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                    Вам ще не призначено рейси.
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
