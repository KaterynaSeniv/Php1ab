@extends('layouts.app')

@section('title', 'Додаткові послуги')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-star me-2 text-warning"></i>Довідник додаткових послуг у поїзді
        </h1>
        <p class="text-muted small mb-0">Керування переліком та тарифами на додаткові послуги</p>
    </div>
    @can('create', App\Models\Service::class)
        <a href="{{ route('services.create') }}" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle me-1"></i>Додати послугу
        </a>
    @endcan
</div>

<div class="card shadow-lg border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-muted small">#</th>
                    <th>Назва послуги</th>
                    <th>Slug</th>
                    <th>Вартість</th>
                    <th>Замовлено у квитках</th>
                    <th style="width: 200px;">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td class="text-muted small">{{ $service->id }}</td>
                        <td class="fw-semibold">{{ $service->name }}</td>
                        <td><code class="small">{{ $service->slug }}</code></td>
                        <td class="fw-bold text-warning">{{ number_format((float) $service->price, 2) }} грн</td>
                        <td><span class="badge bg-secondary"><i class="bi bi-cart me-1"></i>{{ $service->tickets_count }} шт.</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('services.show', $service->id) }}" class="btn btn-outline-info" title="Перегляд">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('update', $service)
                                    <a href="{{ route('services.edit', $service->id) }}" class="btn btn-outline-warning" title="Редагувати">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $service)
                                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Видалити цю послугу?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Видалити">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-star display-4 d-block mb-2"></i>
                            Додаткових послуг ще не додано.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $services->links('pagination::bootstrap-5') }}
</div>
@endsection