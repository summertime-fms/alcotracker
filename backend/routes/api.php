<?php

use App\Http\Controllers\AlcoholEntryController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты аутентификации
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

// Защищенные маршруты (требуют аутентификации)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Маршруты для учета алкоголя
    Route::prefix('alcohol-entries')->group(function () {
        Route::get('/types', [AlcoholEntryController::class, 'types']);
        Route::get('/statistics', [AlcoholEntryController::class, 'statistics']);
        Route::get('/statistics/detailed', [AlcoholEntryController::class, 'detailedStatistics']);
        Route::get('/statistics/pure-alcohol', [AlcoholEntryController::class, 'pureAlcoholStatistics']);
        Route::get('/statistics/weekday', [AlcoholEntryController::class, 'weekdayStatistics']);
        Route::get('/', [AlcoholEntryController::class, 'index']);
        Route::post('/', [AlcoholEntryController::class, 'store']);
        Route::get('/{id}', [AlcoholEntryController::class, 'show']);
        Route::put('/{id}', [AlcoholEntryController::class, 'update']);
        Route::patch('/{id}', [AlcoholEntryController::class, 'update']);
        Route::delete('/{id}', [AlcoholEntryController::class, 'destroy']);
    });
});

// Тестовый маршрут
Route::get('/test', function () {
    return response()->json(['message' => 'Hello from Laravel!']);
});
