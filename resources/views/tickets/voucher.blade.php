@extends('layouts.app')

@section('title', 'Ваучер квитка #' . $ticket->id)

@section('content')
<div class="container py-5">
    <div class="card shadow-lg mx-auto border-0" style="max-width: 800px;">
        <div class="card-header bg-success text-white text-center py-4">
            <h2 class="h2 mb-0">
                <i class="bi bi-check-circle me-2"></i>ВАУЧЕР КВИТКА
            </h2>
            <small class="d-block mt-2 fs-6">Підтвердження оплати</small>
        </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <span class="badge bg-success fs-5 px-4 py-2">№{{ $ticket->id }}</span>
                <span class="badge bg-info fs-5 px-4 py-2 ms-3">ОПЛАЧЕНО</span>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-light">
                        <h5 class="alert-heading mb-3">
                            <i class="bi bi-person me-2"></i>Пасажир
                        </h5>
                        <p class="mb-0 fs-5 fw-bold">{{ $ticket->passenger_full_name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-light">
                        <h5 class="alert-heading mb-3">
                            <i class="bi bi-train me-2"></i>Рейс
                        </h5>
                        @if ($ticket->trainRoute)
                            <p class="mb-1 fw-bold">№{{ $ticket->trainRoute->train_number }}</p>
                            <p class="mb-0 text-muted">{{ $ticket->trainRoute->departure_station }} — {{ $ticket->trainRoute->arrival_station }}</p>
                        @else
                            <p class="mb-0">—</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-light">
                        <h5 class="alert-heading mb-3">
                            <i class="bi bi-clock me-2"></i>Відправлення
                        </h5>
                        @if ($ticket->trainRoute)
                            <p class="mb-0 fs-5 fw-bold">{{ $ticket->trainRoute->formattedDeparture() }}</p>
                        @else
                            <p class="mb-0">—</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-light">
                        <h5 class="alert-heading mb-3">
                            <i class="bi bi-geo-alt me-2"></i>Місце
                        </h5>
                        <p class="mb-0 fs-5 fw-bold">Вагон №{{ $ticket->car_number }}, місце №{{ $ticket->seat_number }}</p>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="alert-heading mb-0">
                            <i class="bi bi-currency-euro me-2"></i>Загальна вартість
                        </h5>
                    </div>
                    <div class="fs-3 fw-bold">{{ number_format((float) $ticket->price, 2) }} грн</div>
                </div>
            </div>

            @if ($ticket->services->count() > 0)
                <div class="alert alert-light mb-4">
                    <h5 class="alert-heading mb-3">
                        <i class="bi bi-star me-2"></i>Додаткові послуги
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($ticket->services as $service)
                            <span class="badge bg-light text-dark border px-3 py-2">
                                {{ $service->name }} (+{{ number_format((float) $service->price, 2) }} грн)
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="text-center text-muted">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Дата оформлення: {{ $ticket->created_at->format('d.m.Y H:i') }}
                </small>
            </div>
        </div>
        <div class="card-footer bg-white text-center py-4">
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('tickets.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-left me-1"></i>До списку квитків
                </a>
                <button onclick="window.print()" class="btn btn-secondary btn-lg">
                    <i class="bi bi-printer me-1"></i>Друкувати
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
