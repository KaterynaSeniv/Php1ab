@extends('layouts.app')

@section('title', $service->name)

@section('content')
<div class="card shadow-sm mx-auto border-0" style="max-width: 640px;">
    <div class="card-header bg-warning text-dark">
        <h2 class="h5 mb-0">
            <i class="bi bi-star-fill me-2"></i>Додаткова послуга: {{ $service->name }}
        </h2>
    </div>
    <div class="card-body">
        <p class="mb-2"><strong class="text-muted">Код (slug):</strong> <code>{{ $service->slug }}</code></p>
        <p class="mb-2"><strong class="text-muted">Тариф:</strong> <span class="fw-bold text-primary fs-5">{{ number_format((float) $service->price, 2) }} грн</span></p>
        <p class="mb-0"><strong class="text-muted">Кількість квитків із цією послугою:</strong> <span class="badge bg-secondary">{{ $service->tickets->count() }}</span></p>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        @can('update', $service)
            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>Редагувати
            </a>
        @endcan
        <a href="{{ route('services.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Назад до списку
        </a>
    </div>
</div>
@endsection