<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Internal\JwtValidationController;
use App\Http\Controllers\Internal\JwtPublicKeyController;
use App\Http\Controllers\Internal\HealthCheckController;

/*
|--------------------------------------------------------------------------
| Internal API Routes (для межсервисной коммуникации)
|--------------------------------------------------------------------------
|
| Эти маршруты используются только для взаимодействия между сервисами
| (API Gateway, другие микросервисы). Доступ должен быть ограничен
| через IP whitelist или mTLS на уровне инфраструктуры.
|
*/

Route::prefix('internal')->group(function () {
    
    // Валидация JWT токена для API Gateway
    // Возвращает 204 + заголовки X-User-Id, X-Scopes, X-Subscription-Tier, X-Email-Verified
    Route::get('/jwt/validate', JwtValidationController::class)->name('internal.jwt.validate');
    
    // Получение публичного RSA ключа для валидации токенов другими сервисами
    Route::get('/jwt/public-key', JwtPublicKeyController::class)->name('internal.jwt.public-key');
    
    // Health check для internal коммуникации
    Route::get('/health', HealthCheckController::class)->name('internal.health');
});

