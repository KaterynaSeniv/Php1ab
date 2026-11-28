<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TicketApiController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthCheckController::class)->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::apiResource('tickets', TicketApiController::class)->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::apiResource('tickets', TicketApiController::class)->except(['index', 'show']);
    });
});
