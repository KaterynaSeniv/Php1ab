@extends('layouts.app')

@section('title', 'Квиток #' . $ticket->id)

@section('content')
<div class="card shadow-lg mx-auto border-0" style="max-width: 800px;">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="h4 mb-0">
            <i class="bi bi-ticket-perforated me-2"></i>Деталі залізничного квитка #{{ $ticket->id }}
        </h2>
        <span class="badge {{ $ticket->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
            <i class="bi {{ $ticket->status === 'paid' ? 'bi-check-circle' : 'bi-clock' }} me-1"></i>
            {{ $ticket->status === 'paid' ? 'Оплачено' : 'Заброньовано' }}
        </span>
    </div>
    <div class="card-body">
        <div class="text-center border-bottom pb-4 mb-4">
            <div id="ticketQr" class="d-inline-block p-3 bg-white border rounded"></div>
            <div class="small text-muted mt-2">QR-код для перевірки посадки</div>
            <div class="font-monospace small text-break">{{ $ticket->qr_token }}</div>
        </div>
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">ПІБ пасажира:</dt>
            <dd class="col-sm-8 fw-semibold">{{ $ticket->passenger_full_name }}</dd>

            <dt class="col-sm-4 text-muted">Рейс поїзда:</dt>
            <dd class="col-sm-8">
                @if ($ticket->trainRoute)
                    <span class="badge bg-primary">№{{ $ticket->trainRoute->train_number }}</span>
                    <div class="fw-semibold">{{ $ticket->trainRoute->departure_station }} — {{ $ticket->trainRoute->arrival_station }}</div>
                    <div class="small text-muted"><i class="bi bi-clock me-1"></i>Відправлення: {{ $ticket->trainRoute->formattedDeparture() }}</div>
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-4 text-muted">Вагон та місце:</dt>
            <dd class="col-sm-8">
                <i class="bi bi-train me-1 text-muted"></i>Вагон №{{ $ticket->car_number }}, місце №{{ $ticket->seat_number }}
            </dd>

            <dt class="col-sm-4 text-muted">Вартість квитка:</dt>
            <dd class="col-sm-8 fw-bold text-primary fs-5">{{ number_format((float) $ticket->price, 2) }} грн</dd>

            <dt class="col-sm-4 text-muted">Додаткові послуги (М:М):</dt>
            <dd class="col-sm-8">
                @forelse ($ticket->services as $service)
                    <span class="badge bg-light text-dark border me-1">{{ $service->name }} ({{ number_format((float) $service->price, 2) }} грн)</span>
                @empty
                    <span class="text-muted">Додаткові послуги не замовлялися</span>
                @endforelse
            </dd>
        </dl>
    </div>
    <div class="card-footer bg-white d-flex gap-3">
        @if ($ticket->status === 'booked')
            @can('pay', $ticket)
                <form action="{{ route('tickets.pay', $ticket->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-credit-card me-1"></i>Оплатити квиток
                    </button>
                </form>
            @endcan
        @endif
        @can('update', $ticket)
            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning btn-lg">
                <i class="bi bi-pencil me-1"></i>Редагувати
            </a>
        @endcan
        <a href="{{ route('tickets.index') }}" class="btn btn-light btn-lg">
            <i class="bi bi-arrow-left me-1"></i>Назад до списку
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof QRCode === 'undefined') {
        return;
    }
    new QRCode(document.getElementById('ticketQr'), {
        text: @json(route('conductor.scan', $ticket->qr_token)),
        width: 180,
        height: 180,
        correctLevel: QRCode.CorrectLevel.M
    });
});
</script>
@endsection