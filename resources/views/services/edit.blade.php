@extends('layouts.app')

@section('title', 'Редагувати послугу')

@section('content')
<div class="card shadow-sm mx-auto border-0" style="max-width: 560px;">
    <div class="card-header bg-warning text-dark">
        <h2 class="h4 mb-0">
            <i class="bi bi-pencil me-2"></i>Редагування послуги «{{ $service->name }}»
        </h2>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Будь ласка, виправте помилки у формі нижче.
            </div>
        @endif

        <form action="{{ route('services.update', $service->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-form.input name="name" label="Назва послуги" :value="$service->name" />
            <x-form.input name="price" label="Вартість (грн)" type="number" step="0.01" min="0" :value="$service->price" />

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle me-1"></i>Оновити послугу
                </button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
