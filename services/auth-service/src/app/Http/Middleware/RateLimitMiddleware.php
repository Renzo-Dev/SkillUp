<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
  public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
  {
    $key = 'rate_limit:' . $request->ip() . ':' . $request->route()->getName();

    $attempts = Cache::get($key, 0);

    if ($attempts >= $maxAttempts) {
      return response()->json([
        'message' => 'Too many attempts. Please try again later.',
        'retry_after' => $decayMinutes * 60
      ], 429);
    }

    Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

    return $next($request);
  }
}
