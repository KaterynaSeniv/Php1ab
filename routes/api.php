<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/tickets', [TicketApiController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketApiController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/tickets', [TicketApiController::class, 'store']);
        Route::put('/tickets/{ticket}', [TicketApiController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketApiController::class, 'destroy']);
    });
});
