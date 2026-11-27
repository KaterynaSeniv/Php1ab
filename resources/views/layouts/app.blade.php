<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Електронна система продажу залізничних квитків')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg mb-5">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-train-front me-2"></i>УкрЗалізниця • Квитки
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house-door me-1"></i>Головна
                    </a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                            <i class="bi bi-ticket me-1"></i>
                            {{ auth()->user()->role === 'passenger' ? 'Мої квитки' : 'Квитки' }}
                        </a>
                    </li>
                @endauth
                @if (auth()->check() && in_array(auth()->user()->role, ['conductor', 'admin'], true))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('conductor.*') ? 'active' : '' }}" href="{{ route('conductor.dashboard') }}">
                            <i class="bi bi-person-badge me-1"></i>Кабінет провідника
                        </a>
                    </li>
                @endif
                @if (auth()->check() && auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-shield-lock me-1"></i>Адмін-панель
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('routes.*') ? 'active' : '' }}" href="{{ route('routes.index') }}">
                        <i class="bi bi-geo-alt me-1"></i>Рейси поїздів
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                        <i class="bi bi-star me-1"></i>Додаткові послуги
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Увійти</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Реєстрація</a>
                @endguest
                @auth
                    <div class="dropdown user-dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle user-dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    @if (Auth::user()->role === 'admin')
                                        Адміністратор
                                    @elseif (Auth::user()->role === 'conductor')
                                        Провідник
                                    @else
                                        Пасажир
                                    @endif
                                </span>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Мій профіль</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Вийти
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container flex-grow-1">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer>
    <div class="container text-center">
        <p class="mb-2">
            <i class="bi bi-train-front me-2"></i>Електронна система продажу залізничних квитків
        </p>
        <small>Лабораторна робота №7 (Laravel Blade SSR)</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>