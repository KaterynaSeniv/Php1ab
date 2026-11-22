@extends('layouts.app')

@section('title', 'Додати залізничний рейс')

@section('content')
<div class="card shadow-lg mx-auto border-0" style="max-width: 700px;">
    <div class="card-header bg-success text-white">
        <h2 class="h4 mb-0">
            <i class="bi bi-plus-circle me-2"></i>Створення нового залізничного рейсу
        </h2>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>Виправте помилки у формі нижче.
            </div>
        @endif

        <form action="{{ route('routes.store') }}" method="POST">
            @csrf
            <x-form.input name="train_number" label="Номер поїзда" placeholder="Наприклад: 043К" />
            <x-form.input name="departure_station" label="Станція відправлення" placeholder="Київ-Пасажирський" />
            <x-form.input name="arrival_station" label="Станція прибуття" placeholder="Івано-Франківськ" />
            <x-form.input name="departure_time" label="Дата та час відправлення" type="datetime-local" />
            <x-form.input name="base_price" label="Базова вартість квитка (грн)" type="number" step="0.01" min="0" placeholder="350.00" />
            
            <div class="row">
                <div class="col-md-4">
                    <x-form.input name="car_count" label="Кількість вагонів" type="number" min="1" max="25" placeholder="10" />
                </div>
                <div class="col-md-4">
                    <x-form.input name="seats_per_car" label="Місць у вагоні" type="number" min="1" max="54" placeholder="54" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Тип вагонів</label>
                    <select name="car_type" class="form-select @error('car_type') is-invalid @enderror">
                        <option value="platskart" {{ old('car_type', 'platskart') === 'platskart' ? 'selected' : '' }}>Плацкарт</option>
                        <option value="coupe" {{ old('car_type') === 'coupe' ? 'selected' : '' }}>Купе</option>
                    </select>
                    @error('car_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-1"></i>Зберегти рейс
                </button>
                <a href="{{ route('routes.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection