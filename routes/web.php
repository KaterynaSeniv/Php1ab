<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\SentryTestController;
use App\Http\Controllers\Web\RouteScheduleController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/search', [RouteScheduleController::class, 'search'])->name('home.search');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

Route::post('/logout', [WebAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [WebAuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [WebAuthController::class, 'updateProfile'])->name('profile.update');

    Route::resource('tickets', TicketController::class);
    Route::put('/tickets/{ticket}/pay', [TicketController::class, 'pay'])->name('tickets.pay');
    Route::get('/tickets/{ticket}/voucher', [TicketController::class, 'voucher'])->name('tickets.voucher');
    Route::get('/sentry/test', SentryTestController::class)->name('sentry.test');
});

Route::middleware('auth')->prefix('conductor')->name('conductor.')->group(function () {
    Route::get('/dashboard', [ConductorController::class, 'dashboard'])->name('dashboard');
    Route::get('/routes/{route}', [ConductorController::class, 'route'])->name('route');
    Route::put('/tickets/{ticket}/check-in', [ConductorController::class, 'checkIn'])->name('check-in');
    Route::get('/scan/{token}', [ConductorController::class, 'scan'])->name('scan');
    Route::post('/scan/{token}/confirm', [ConductorController::class, 'confirmScan'])->name('scan.confirm');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::post('/routes/{route}/conductors', [AdminController::class, 'assignConductor'])->name('routes.conductors');
    Route::delete('/routes/{route}/conductors/{conductor}', [AdminController::class, 'removeConductor'])->name('routes.conductors.remove');
});

Route::middleware('auth')->group(function () {
    Route::resource('routes', RouteScheduleController::class)->except(['index', 'show']);
    Route::resource('services', ServiceController::class)->except(['index', 'show']);
});

Route::resource('routes', RouteScheduleController::class)->only(['index', 'show']);
Route::resource('services', ServiceController::class)->only(['index', 'show']);
