<?php

declare(strict_types=1);

use App\Http\Controllers\RouteScheduleController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => response()->json([
    'status' => 'OK',
    'service' => 'Railway Ticket Sales System',
    'timestamp' => now(),
]));

Route::get('/routes', [RouteScheduleController::class, 'index'])->name('routes.index');

Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::post('/', [TicketController::class, 'store'])
        ->middleware('ticket.access')
        ->name('store');
    Route::get('/{id}', [TicketController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
});
