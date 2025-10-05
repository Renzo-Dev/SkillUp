<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;

Route::group(['prefix' => 'auth'], function () {
    // Публичные роуты (без аутентификации)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Роуты для обновления токенов (публичные, но требуют refresh token)
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Роуты для верификации email (публичные)
    Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail']);
    Route::post('/resend-verification', [EmailVerificationController::class, 'resendVerification']);
    Route::get('/verification-status', [EmailVerificationController::class, 'checkVerificationStatus']);
    
    // Защищенные роуты (требуют JWT токен)
    Route::middleware('guard.jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Системные роуты
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'service' => 'auth-service'
    ]);
});

// Роуты для мониторинга (если нужно)
Route::get('/status', function () {
    return response()->json([
        'service' => 'auth-service',
        'version' => '1.0.0',
        'status' => 'running',
        'database' => 'connected'
    ]);
});