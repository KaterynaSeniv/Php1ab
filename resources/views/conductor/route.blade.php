@extends('layouts.app')

@section('title', 'Рейс №' . $route->train_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('conductor.dashboard') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>До розкладу провідника
        </a>
        <h1 class="h3 mt-2 mb-1">Рейс №{{ $route->train_number }}</h1>
        <p class="text-muted mb-0">{{ $route->departure_station }} — {{ $route->arrival_station }}</p>
    </div>
    <span class="badge bg-primary fs-6">{{ $route->formattedDeparture() }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Квитків</div><div class="h4 mb-0">{{ $statistics['total'] }}</div></div></div></div>
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Оплачено</div><div class="h4 mb-0 text-success">{{ $statistics['paid'] }}</div></div></div></div>
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Перевірено</div><div class="h4 mb-0 text-primary">{{ $statistics['checked'] }}</div></div></div></div>
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Очікують</div><div class="h4 mb-0 text-warning">{{ $statistics['not_checked'] }}</div></div></div></div>
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Вільно місць</div><div class="h4 mb-0">{{ $statistics['available'] }}</div></div></div></div>
    <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><div class="small text-muted">Мої вагони</div><div class="h5 mb-0">{{ $assignment->cars_scope }}</div></div></div></div>
</div>

<div class="card border-0 shadow-lg mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between gap-3">
        <div>
            <div class="small text-muted">Зміна</div>
            <strong>{{ \Carbon\Carbon::parse($assignment->shift_start)->format('d.m.Y H:i') }} — {{ \Carbon\Carbon::parse($assignment->shift_end)->format('d.m.Y H:i') }}</strong>
        </div>
        <div class="flex-grow-1" style="max-width: 520px;">
            <div class="small text-muted">Інструкція</div>
            <strong>{{ $assignment->instructions ?: 'Додаткових інструкцій немає.' }}</strong>
        </div>
    </div>
</div>

<div class="card border-0 shadow-lg">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h2 class="h5 mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Пасажирський список</h2>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
            <i class="bi bi-qr-code-scan me-1"></i>Сканувати QR-квиток
        </button>
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="ПІБ, вагон або місце">
            <select name="boarding_status" class="form-select">
                <option value="">Усі статуси</option>
                <option value="not_checked" @selected(request('boarding_status') === 'not_checked')>Не перевірено</option>
                <option value="checked" @selected(request('boarding_status') === 'checked')>Перевірено</option>
                <option value="problem" @selected(request('boarding_status') === 'problem')>Проблема</option>
            </select>
            <button type="submit" class="btn btn-primary">Знайти</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Квиток</th>
                    <th>Пасажир</th>
                    <th>Вагон / місце</th>
                    <th>Послуги</th>
                    <th>Оплата</th>
                    <th>Посадка</th>
                    <th class="text-end">Дія</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td><strong>#{{ $ticket->id }}</strong></td>
                        <td>
                            <strong>{{ $ticket->passenger_full_name }}</strong>
                            @if ($ticket->checked_at)
                                <div class="small text-muted">Перевірено {{ $ticket->checked_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $ticket->car_number }} / {{ $ticket->seat_number }}</span></td>
                        <td>
                            @forelse ($ticket->services as $service)
                                <span class="badge bg-secondary mb-1">{{ $service->name }}</span>
                            @empty
                                <span class="text-muted small">Немає</span>
                            @endforelse
                        </td>
                        <td>
                            @if ($ticket->status === 'paid')
                                <span class="badge bg-success">Оплачено</span>
                            @else
                                <span class="badge bg-warning text-dark">Заброньовано</span>
                            @endif
                        </td>
                        <td>
                            @if ($ticket->boarding_status === 'checked')
                                <span class="badge bg-primary">Перевірено</span>
                            @elseif ($ticket->boarding_status === 'problem')
                                <span class="badge bg-danger">Проблема</span>
                            @else
                                <span class="badge bg-light text-dark border">Не перевірено</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('conductor.check-in', $ticket) }}" method="POST" class="d-inline-flex gap-1">
                                @csrf
                                @method('PUT')
                                <select name="boarding_status" class="form-select form-select-sm" style="width: 145px;">
                                    <option value="checked" @selected($ticket->boarding_status === 'checked')>Перевірено</option>
                                    <option value="problem" @selected($ticket->boarding_status === 'problem')>Проблема</option>
                                    <option value="not_checked" @selected($ticket->boarding_status === 'not_checked')>Скинути</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Зберегти</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Квитків за вибраними фільтрами не знайдено.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="qrScannerModalLabel">Сканування QR-квитка</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-error" class="text-danger small mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let scanner = null;

    document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function () {
        if (typeof Html5Qrcode === 'undefined') {
            document.getElementById('qr-reader-error').textContent = 'Сканер QR не завантажено.';
            return;
        }

        scanner = new Html5Qrcode('qr-reader');
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            function (decodedText) {
                scanner.stop().then(function () {
                    window.location.href = decodedText;
                }).catch(function () {});
            },
            function () {}
        ).catch(function (error) {
            document.getElementById('qr-reader-error').textContent = 'Немає доступу до камери: ' + error;
        });
    });

    document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function () {
        if (scanner) {
            scanner.stop().catch(function () {});
            scanner = null;
        }
    });
});
</script>
@endsection
