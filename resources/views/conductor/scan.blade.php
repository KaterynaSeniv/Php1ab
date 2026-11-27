@extends('layouts.app')

@section('title', 'Перевірка QR-квитка')

@section('content')
<div class="card shadow-lg mx-auto border-0" style="max-width: 760px;">
    <div class="card-header {{ $ticket->boarding_status === 'checked' ? 'bg-success' : ($ticket->status === 'paid' ? 'bg-primary' : 'bg-warning text-dark') }} text-white">
        <h1 class="h4 mb-0">
            <i class="bi bi-qr-code-scan me-2"></i>Перевірка QR-квитка
        </h1>
    </div>
    <div class="card-body p-4">
        @if ($ticket->boarding_status === 'checked')
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>Посадку вже підтверджено.
            </div>
        @elseif ($ticket->status !== 'paid')
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>Квиток броньовано, але ще не оплачено.
            </div>
        @endif

        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Квиток</dt>
            <dd class="col-sm-8 fw-bold">#{{ $ticket->id }}</dd>
            <dt class="col-sm-4 text-muted">Пасажир</dt>
            <dd class="col-sm-8 fw-bold">{{ $ticket->passenger_full_name }}</dd>
            <dt class="col-sm-4 text-muted">Рейс</dt>
            <dd class="col-sm-8">№{{ $ticket->trainRoute->train_number }} · {{ $ticket->trainRoute->departure_station }} — {{ $ticket->trainRoute->arrival_station }}</dd>
            <dt class="col-sm-4 text-muted">Відправлення</dt>
            <dd class="col-sm-8">{{ $ticket->trainRoute->formattedDeparture() }}</dd>
            <dt class="col-sm-4 text-muted">Місце</dt>
            <dd class="col-sm-8"><span class="badge bg-primary fs-6">Вагон {{ $ticket->car_number }} / місце {{ $ticket->seat_number }}</span></dd>
            <dt class="col-sm-4 text-muted">Оплата</dt>
            <dd class="col-sm-8">
                @if ($ticket->status === 'paid')
                    <span class="badge bg-success">Оплачено · {{ number_format((float) $ticket->price, 2) }} грн</span>
                @else
                    <span class="badge bg-warning text-dark">Не оплачено</span>
                @endif
            </dd>
        </dl>
    </div>
    <div class="card-footer bg-white d-flex gap-2 justify-content-between">
        <a href="{{ route('conductor.route', $ticket->trainRoute) }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i>До рейсу
        </a>
        @if ($ticket->status === 'paid' && $ticket->boarding_status !== 'checked')
            <form action="{{ route('conductor.scan.confirm', $ticket->qr_token) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle me-1"></i>Підтвердити посадку
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
