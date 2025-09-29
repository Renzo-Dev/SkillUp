<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// API маршруты для аутентификации
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Тестовый маршрут для проверки работы API
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'service' => 'auth-service']);
});
