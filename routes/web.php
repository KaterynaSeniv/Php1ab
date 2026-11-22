<?php

declare(strict_types=1);

use App\Http\Controllers\RouteScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/search', [RouteScheduleController::class, 'search'])->name('home.search');

Route::resource('routes', RouteScheduleController::class);
Route::resource('services', ServiceController::class);
Route::resource('tickets', TicketController::class);
Route::put('/tickets/{ticket}/pay', [TicketController::class, 'pay'])->name('tickets.pay');
Route::get('/tickets/{ticket}/voucher', [TicketController::class, 'voucher'])->name('tickets.voucher');
