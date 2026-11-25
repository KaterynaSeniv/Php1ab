@extends('layouts.app')

@section('title', 'Редагування квитка #' . $ticket->id)

@section('content')
<div class="card shadow-lg mx-auto border-0" style="max-width: 900px;">
    <div class="card-header bg-primary text-white">
        <h2 class="h4 mb-0">
            <i class="bi bi-pencil me-2"></i>Редагування квитка #{{ $ticket->id }}
        </h2>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>Будь ласка, виправте помилки у формі нижче.
            </div>
        @endif

        <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" id="ticketForm">
            @csrf
            @method('PUT')

            <!-- Вибір рейсу поїзда (1:М) -->
            <div class="mb-4">
                <label for="train_route_id" class="form-label">
                    <i class="bi bi-train me-1 text-primary"></i>Рейс поїзда
                </label>
                <select name="train_route_id" id="train_route_id" class="form-select @error('train_route_id') is-invalid @enderror">
                    <option value="">-- Оберіть залізничний рейс --</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}"
                                data-base-price="{{ $route->base_price }}"
                                data-car-count="{{ $route->car_count }}"
                                data-seats-per-car="{{ $route->seats_per_car }}"
                                data-car-type="{{ $route->car_type }}"
                                {{ (string) old('train_route_id', $ticket->train_route_id) === (string) $route->id ? 'selected' : '' }}>
                            №{{ $route->train_number }}: {{ $route->departure_station }} — {{ $route->arrival_station }} ({{ $route->formattedDeparture() }}) - {{ number_format((float) $route->base_price, 2) }} грн
                        </option>
                    @endforeach
                </select>
                @error('train_route_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <x-form.input name="passenger_full_name" label="ПІБ пасажира" :value="$ticket->passenger_full_name" />

            <!-- Візуальний вибір місця -->
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-geo-alt me-1 text-primary"></i>Вибір місця
                </label>
                
                <!-- Вибір вагона -->
                <div class="mb-3">
                    <select id="carSelect" class="form-select">
                        <option value="">-- Оберіть вагон --</option>
                    </select>
                </div>
                
                <!-- Схема місць обраного вагона -->
                <div id="seatMap" class="p-3 bg-light rounded border">
                    <p class="text-muted text-center mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Спочатку оберіть рейс, потім вагон для відображення місць
                    </p>
                </div>
                
                <!-- Приховані поля для відправки -->
                <input type="hidden" name="car_number" id="selectedCar" value="{{ old('car_number', $ticket->car_number) }}">
                <input type="hidden" name="seat_number" id="selectedSeat" value="{{ old('seat_number', $ticket->seat_number) }}">
                @error('car_number')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('seat_number')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Вибір додаткових послуг (М:М) -->
            <div class="mb-4">
                <label class="form-label d-block">
                    <i class="bi bi-star me-1 text-primary"></i>Додаткові послуги у поїзді
                </label>
                @php
                    $selectedServices = array_map('strval', old('services', $ticket->services->pluck('id')->all()));
                @endphp
                @foreach ($services as $service)
                    <div class="form-check form-check-inline mb-2">
                        <input
                            class="form-check-input service-checkbox"
                            type="checkbox"
                            name="services[]"
                            id="service_{{ $service->id }}"
                            value="{{ $service->id }}"
                            data-price="{{ $service->price }}"
                            {{ in_array((string) $service->id, $selectedServices, true) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="service_{{ $service->id }}">
                            {{ $service->name }} (+{{ number_format((float) $service->price, 2) }} грн)
                        </label>
                    </div>
                @endforeach
                @error('services')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Live Preview загальної вартості -->
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-calculator me-2"></i>
                        <strong>Загальна вартість квитка:</strong>
                    </div>
                    <div class="text-end">
                        <span class="display-6 fw-bold text-primary" id="totalPrice">{{ number_format((float) $ticket->price, 2) }}</span>
                        <span class="text-muted">грн</span>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Базова вартість: <span id="basePrice">0.00</span> грн + 
                    Послуги: <span id="servicesPrice">0.00</span> грн
                </div>
            </div>

            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-1"></i>Оновити квиток
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.seats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    max-width: 400px;
    margin: 0 auto;
}

.seats-grid.coupe {
    grid-template-columns: repeat(4, 1fr);
}

.seat {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8f9fa;
}

.seat:hover:not(.booked) {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.seat.available {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.seat.booked {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
    cursor: not-allowed;
    opacity: 0.6;
}

.seat.selected {
    background: #007bff;
    border-color: #0056b3;
    color: white;
    transform: scale(1.15);
    box-shadow: 0 2px 8px rgba(0,123,255,0.4);
}

.seat-legend {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.legend-box {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 2px solid;
}

.car-info {
    text-align: center;
    margin-bottom: 15px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}
</style>

@include('tickets.partials.seat-picker-script')
@endsection
