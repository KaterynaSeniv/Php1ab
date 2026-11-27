@extends('layouts.app')

@section('title', 'Мій профіль')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-lg border-0 mb-4">
            <div class="card-body text-center p-4">
                <div class="display-4 text-primary mb-3">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h1 class="h4">{{ $user->name }}</h1>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                @php
                    $roles = [
                        'passenger' => ['Пасажир', 'bg-primary'],
                        'conductor' => ['Провідник', 'bg-warning text-dark'],
                        'admin' => ['Адміністратор', 'bg-danger'],
                    ];
                @endphp
                <span class="badge {{ $roles[$user->role][1] ?? 'bg-secondary' }}">
                    {{ $roles[$user->role][0] ?? $user->role }}
                </span>
            </div>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Редагувати профіль</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="profile-name" class="form-label">Ім'я</label>
                        <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="profile-email" class="form-label">Email</label>
                        <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="profile-password" class="form-label">Новий пароль</label>
                        <input id="profile-password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Не змінювати, якщо порожньо">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="profile-password-confirmation" class="form-label">Підтвердження пароля</label>
                        <input id="profile-password-confirmation" type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i>Зберегти
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">
                    <i class="bi bi-ticket-perforated me-2"></i>Мої квитки
                </h2>
                <span class="badge bg-light text-dark">{{ $tickets->total() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Рейс</th>
                                <th>Місце</th>
                                <th>Вартість</th>
                                <th>Статус</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td>
                                        @if ($ticket->trainRoute)
                                            №{{ $ticket->trainRoute->train_number }}<br>
                                            <small class="text-muted">{{ $ticket->trainRoute->departure_station }} — {{ $ticket->trainRoute->arrival_station }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>Вагон {{ $ticket->car_number }}, місце {{ $ticket->seat_number }}</td>
                                    <td>{{ number_format((float) $ticket->price, 2) }} грн</td>
                                    <td>
                                        @if ($ticket->status === 'paid')
                                            <span class="badge bg-success">Оплачено</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Заброньовано</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">У вас ще немає квитків.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tickets->hasPages())
                    <div class="p-3">{{ $tickets->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
