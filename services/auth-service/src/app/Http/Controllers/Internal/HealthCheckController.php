<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    /**
     * Health check для internal коммуникации
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'OK',
            'service' => 'auth-service-internal',
            'timestamp' => now()->toISOString(),
        ]);
    }
}

