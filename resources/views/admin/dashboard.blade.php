@extends('layouts.app')

@section('title', 'Панель адміністратора')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="text-uppercase text-danger fw-bold small mb-1">Центр керування системою</p>
        <h1 class="h3 mb-1">Адміністративна панель</h1>
        <p class="text-muted mb-0">Останнє оновлення: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('routes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Новий рейс
        </a>
        <a href="{{ route('services.create') }}" class="btn btn-outline-warning">
            <i class="bi bi-star me-1"></i>Нова послуга
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Користувачі</div><div class="display-6 fw-bold text-primary">{{ $statistics['users'] }}</div><div class="small text-muted">{{ $statistics['conductors'] }} провідники</div></div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Пасажири</div><div class="display-6 fw-bold text-info">{{ $statistics['passengers'] }}</div></div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Рейси</div><div class="display-6 fw-bold text-success">{{ $statistics['routes'] }}</div></div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Квитки</div><div class="display-6 fw-bold text-warning">{{ $statistics['tickets'] }}</div><div class="small text-muted">{{ $statistics['paid_tickets'] }} оплачено</div></div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Послуги</div><div class="display-6 fw-bold">{{ $statistics['services'] }}</div></div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Дохід</div><div class="h3 fw-bold text-success mb-0">{{ number_format($statistics['revenue'], 2) }} грн</div><div class="small text-danger">{{ $statistics['problems'] }} проблем</div></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Користувачі та ролі</h2>
                <span class="badge bg-light text-dark">{{ $statistics['users'] }} у системі</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Користувач</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Квитків</th>
                            <th class="text-end">Змінити роль</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td class="text-muted small">{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span class="badge bg-danger">Адміністратор</span>
                                    @elseif ($user->role === 'conductor')
                                        <span class="badge bg-warning text-dark">Провідник</span>
                                    @else
                                        <span class="badge bg-primary">Пасажир</span>
                                    @endif
                                </td>
                                <td>{{ $user->tickets_count }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="d-inline-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-select form-select-sm" style="width: 150px;">
                                            <option value="passenger" @selected($user->role === 'passenger')>Пасажир</option>
                                            <option value="conductor" @selected($user->role === 'conductor')>Провідник</option>
                                            <option value="admin" @selected($user->role === 'admin')>Адміністратор</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Зберегти</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $users->links('pagination::bootstrap-5') }}</div>
        </div>

        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Останні квитки</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>№</th>
                            <th>Пасажир</th>
                            <th>Рейс</th>
                            <th>Місце</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTickets as $ticket)
                            <tr>
                                <td><strong>#{{ $ticket->id }}</strong></td>
                                <td>{{ $ticket->passenger_full_name }}</td>
                                <td>
                                    @if ($ticket->trainRoute)
                                        №{{ $ticket->trainRoute->train_number }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $ticket->car_number }} / {{ $ticket->seat_number }}</td>
                                <td>
                                    @if ($ticket->boarding_status === 'problem')
                                        <span class="badge bg-danger">Проблема</span>
                                    @elseif ($ticket->boarding_status === 'checked')
                                        <span class="badge bg-primary">Перевірено</span>
                                    @elseif ($ticket->status === 'paid')
                                        <span class="badge bg-success">Оплачено</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Заброньовано</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Квитків ще немає.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Призначення провідників</h2>
                <a href="{{ route('routes.index') }}" class="btn btn-sm btn-outline-primary">Усі рейси</a>
            </div>
            <div class="card-body">
                @foreach ($routes as $route)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="badge bg-primary">№{{ $route->train_number }}</span>
                                <div class="fw-semibold mt-1">{{ $route->departure_station }} → {{ $route->arrival_station }}</div>
                                <div class="small text-muted">{{ $route->formattedDeparture() }} · {{ $route->tickets_count }} квитків</div>
                            </div>
                            <a href="{{ route('routes.edit', $route) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>

                        @forelse ($route->conductors as $conductor)
                            <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2 mb-2">
                                <div>
                                    <strong>{{ $conductor->name }}</strong>
                                    <div class="small text-muted">{{ $conductor->pivot->cars_scope }} · {{ \Carbon\Carbon::parse($conductor->pivot->shift_start)->format('d.m H:i') }}–{{ \Carbon\Carbon::parse($conductor->pivot->shift_end)->format('H:i') }}</div>
                                </div>
                                <form action="{{ route('admin.routes.conductors.remove', [$route, $conductor]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Скасувати призначення">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="small text-muted mb-2">Провідника ще не призначено.</p>
                        @endforelse

                        @if ($conductors->isNotEmpty())
                            <details class="mt-2">
                                <summary class="btn btn-sm btn-outline-primary">Призначити провідника</summary>
                                <form action="{{ route('admin.routes.conductors', $route) }}" method="POST" class="mt-3">
                                    @csrf
                                    <label class="form-label small">Провідник</label>
                                    <select name="conductor_id" class="form-select mb-2" required>
                                        @foreach ($conductors as $conductor)
                                            <option value="{{ $conductor->id }}">{{ $conductor->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small">Початок зміни</label>
                                            <input type="datetime-local" name="shift_start" class="form-control" value="{{ $route->departure_time->copy()->subHour()->format('Y-m-d\TH:i') }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Завершення</label>
                                            <input type="datetime-local" name="shift_end" class="form-control" value="{{ $route->departure_time->copy()->addHours(8)->format('Y-m-d\TH:i') }}" required>
                                        </div>
                                        <div class="col-12">
                                            <input type="text" name="cars_scope" class="form-control" placeholder="Вагони, наприклад: 1–5">
                                        </div>
                                        <div class="col-12">
                                            <textarea name="instructions" class="form-control" rows="2" placeholder="Інструкція провіднику"></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Зберегти призначення</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
                <div class="mt-3">{{ $routes->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
