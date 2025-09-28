<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->prefix('subscription')->group(function () {
    Route::get('/status', [\App\Http\Controllers\SubscriptionStatusController::class, 'show']);
    Route::post('/change', [\App\Http\Controllers\SubscriptionController::class, 'change']);
    Route::post('/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel']);
    Route::get('/limits', [\App\Http\Controllers\SubscriptionController::class, 'limits']);
    Route::post('/usage', [\App\Http\Controllers\SubscriptionController::class, 'usage']);
    Route::get('/history', [\App\Http\Controllers\SubscriptionHistoryController::class, 'index']);
});

Route::middleware(['jwt.auth:'.config('jwt.admin_scope')])->prefix('subscription/admin')->group(function () {
    Route::post('/plans', [\App\Http\Controllers\SubscriptionAdminController::class, 'store']);
    Route::patch('/plans/{plan}', [\App\Http\Controllers\SubscriptionAdminController::class, 'update']);
    Route::post('/subscriptions/{subscription}/adjust', [\App\Http\Controllers\SubscriptionAdminController::class, 'adjust']);
});

