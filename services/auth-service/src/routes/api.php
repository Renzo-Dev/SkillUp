<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

// Auth API routes
Route::prefix('auth')->group(function () {
  // Public routes
  Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
  Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
  Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
  Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);

  // Protected routes
  Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/refresh', [App\Http\Controllers\AuthController::class, 'refresh']);
    Route::get('/me', [App\Http\Controllers\AuthController::class, 'me']);
    Route::put('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile']);
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
  });
});

// User management routes
Route::prefix('users')->middleware('auth:sanctum')->group(function () {
  Route::get('/', [App\Http\Controllers\UserController::class, 'index']);
  Route::get('/{id}', [App\Http\Controllers\UserController::class, 'show']);
  Route::put('/{id}', [App\Http\Controllers\UserController::class, 'update']);
  Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
});
