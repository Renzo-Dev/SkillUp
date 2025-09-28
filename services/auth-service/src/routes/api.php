<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/auth/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'auth-service',
        'timestamp' => now()
    ]);
});

// Public routes
Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/refresh', [AuthController::class, 'refresh']);
  Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
  Route::post('/reset-password', [AuthController::class, 'resetPassword']);
  Route::post('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
});

// Protected routes
Route::prefix('auth')->middleware('jwt.auth')->group(function () {
  Route::get('/me', [AuthController::class, 'me']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::put('/profile', [AuthController::class, 'updateProfile']);
  Route::put('/password', [AuthController::class, 'updatePassword']);
});

// Admin routes
Route::prefix('admin')->middleware(['jwt.auth', 'role:admin'])->group(function () {
  Route::get('/users', [AdminController::class, 'getUsers']);
  Route::put('/users/{id}/role', [AdminController::class, 'updateUserRole']);
  Route::post('/users/{id}/ban', [AdminController::class, 'banUser']);
});
