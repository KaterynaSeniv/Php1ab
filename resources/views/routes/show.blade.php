@extends('layouts.app')

@section('title', 'Деталі рейсу')

@section('content')
<div class="card shadow-lg mb-4 border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h2 class="h4 mb-0">
            <i class="bi bi-train me-2"></i>Деталі рейсу №{{ $route->train_number }}
        </h2>
        <a href="{{ route('routes.index') }}" class="btn btn-light text-success btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Повернутися до списку
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th class="text-muted" style="width: 40%;">Номер поїзда:</th>
                        <td><span class="badge bg-primary fs-6">{{ $route->train_number }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Станція відправлення:</th>
                        <td class="fw-semibold">{{ $route->departure_station }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Станція прибуття:</th>
                        <td class="fw-semibold">{{ $route->arrival_station }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Час відправлення:</th>
                        <td><i class="bi bi-clock me-1 text-muted"></i>{{ $route->formattedDeparture() }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Базова вартість:</th>
                        <td class="fw-bold text-success">{{ number_format((float) $route->base_price, 2) }} грн</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Тип вагонів:</th>
                        <td class="fw-semibold">
                            @if ($route->car_type === 'coupe')
                                <span class="badge bg-primary">Купе</span>
                            @else
                                <span class="badge bg-warning text-dark">Плацкарт</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Кількість вагонів:</th>
                        <td class="fw-semibold">{{ $route->car_count }} вагонів</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Місць у вагоні:</th>
                        <td class="fw-semibold">{{ $route->seats_per_car }} місць</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Загальна місткість:</th>
                        <td class="fw-bold text-primary">{{ $route->car_count * $route->seats_per_car }} місць</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Статистика</h5>
                        <p class="card-text">
                            <span class="display-6 fw-bold text-success">{{ $route->tickets_count }}</span>
                            <br>
                            <small class="text-muted">оформлено квитків</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-lg border-0">
    <div class="card-header bg-white">
        <h4 class="h5 mb-0">
            <i class="bi bi-ticket-perforated me-2 text-primary"></i>Квитки на цей рейс
        </h4>
    </div>
    <div class="card-body">
        @auth
        @if (in_array(auth()->user()->role, ['admin', 'conductor']))
        @forelse ($route->tickets as $ticket)
            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                <div>
                    <span class="fw-semibold">{{ $ticket->passenger_full_name }}</span>
                    <br>
                    <small class="text-muted"><i class="bi bi-train me-1"></i>Вагон {{ $ticket->car_number }}, місце {{ $ticket->seat_number }}</small>
                </div>
                <div class="text-end">
                    <span class="badge {{ $ticket->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                        <i class="bi {{ $ticket->status === 'paid' ? 'bi-check-circle' : 'bi-clock' }} me-1"></i>
                        {{ $ticket->status === 'paid' ? 'Оплачено' : 'Заброньовано' }}
                    </span>
                    <br>
                    <strong>{{ number_format((float) $ticket->price, 2) }} грн</strong>
                </div>
            </div>
        @empty
            <p class="text-center text-muted py-5">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                На цей рейс ще не оформлено жодного квитка.
            </p>
        @endforelse
        @else
        <p class="text-center text-muted py-5">Деталі пасажирів доступні провіднику та адміністратору.</p>
        @endif
        @else
        <p class="text-center text-muted py-5">Увійдіть у систему для перегляду бронювань рейсу.</p>
        @endauth
    </div>
</div>

<div class="mt-3 d-flex gap-3">
    @can('update', $route)
        <a href="{{ route('routes.edit', $route->id) }}" class="btn btn-warning btn-lg">
            <i class="bi bi-pencil me-1"></i>Редагувати рейс
        </a>
    @endcan
    @auth
        <a href="{{ route('tickets.create', ['route_id' => $route->id]) }}" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle me-1"></i>Оформити квиток
        </a>
    @else
        <a href="{{ route('login') }}" class="btn btn-success btn-lg">
            <i class="bi bi-box-arrow-in-right me-1"></i>Увійти для бронювання
        </a>
    @endauth
</div>
@endsection
