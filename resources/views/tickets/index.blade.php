@extends('layouts.app')

@section('title', 'Залізничні квитки')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-ticket-perforated me-2 text-primary"></i>Оформлені залізничні квитки
        </h1>
        <p class="text-muted small mb-0">Управління та перегляд усіх квитків у системі</p>
    </div>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-circle me-1"></i>Оформити новий квиток
    </a>
</div>

<div class="card shadow-lg border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-muted small">#</th>
                    <th>Пасажир</th>
                    <th>Рейс поїзда</th>
                    <th>Вагон / Місце</th>
                    <th>Додаткові послуги</th>
                    <th>Вартість</th>
                    <th>Статус</th>
                    <th style="width: 200px;">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td class="text-muted small">{{ $ticket->id }}</td>
                        <td class="fw-semibold">{{ $ticket->passenger_full_name }}</td>
                        <td>
                            @if ($ticket->trainRoute)
                                <span class="badge bg-primary fs-6">№{{ $ticket->trainRoute->train_number }}</span>
                                <div class="small text-muted">{{ $ticket->trainRoute->departure_station }} — {{ $ticket->trainRoute->arrival_station }} · {{ $ticket->trainRoute->formattedDeparture() }}</div>
                            @else
                                <span class="text-muted">Не вказано</span>
                            @endif
                        </td>
                        <td>
                            <i class="bi bi-train me-1 text-muted"></i>Вагон {{ $ticket->car_number }}, місце {{ $ticket->seat_number }}
                        </td>
                        <td>
                            @forelse ($ticket->services as $service)
                                <span class="badge bg-light text-dark border">{{ $service->name }}</span>
                            @empty
                                <span class="text-muted small">Без послуг</span>
                            @endforelse
                        </td>
                        <td class="fw-bold text-primary">{{ number_format((float) $ticket->price, 2) }} грн</td>
                        <td>
                            @if ($ticket->status === 'paid')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Оплачено</span>
                            @else
                                <span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Заброньовано</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-outline-info" title="Перегляд">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-outline-warning" title="Редагувати">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Ви дійсно бажаєте видалити цей квиток?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Видалити">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            Квитків у системі ще не оформлено.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $tickets->links('pagination::bootstrap-5') }}
</div>
@endsection