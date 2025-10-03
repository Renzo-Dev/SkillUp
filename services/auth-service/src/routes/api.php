<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Middleware\JwtAuthMiddleware;

// Публичные маршруты (без аутентификации)
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/refresh', [TokenController::class, 'refresh'])->name('refresh');
// Верификация email: публичный JSON-эндпоинт подтверждения по токену
Route::post('/email/verify', [EmailVerificationController::class, 'verifyJson'])
    ->middleware('throttle:30,1')
    ->name('email.verify.json');

// Тестовый маршрут для проверки работы API
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'service' => 'auth-service']);
});

// Защищенные маршруты (требуют аутентификации)
Route::middleware([JwtAuthMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    
    // Работа с токенами
    Route::post('/token/revoke', [TokenController::class, 'revoke'])->name('token.revoke');
    Route::get('/token/validate', [TokenController::class, 'validate'])->name('token.validate');
    
    // Верификация email: защищенные маршруты (с троттлингом по умолчанию)
    Route::post('/email/send-verification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('email.send');

    Route::post('/email/resend-verification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('email.resend');
});
