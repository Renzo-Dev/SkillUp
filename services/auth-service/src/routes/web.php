<?php

use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'auth-service',
        'timestamp' => now()
    ]);
});
