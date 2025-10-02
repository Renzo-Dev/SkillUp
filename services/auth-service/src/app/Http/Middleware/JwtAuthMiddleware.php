<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Contracts\JwtServiceInterface;
use App\Contracts\BlacklistServiceInterface;
use Illuminate\Support\Facades\Log;

class JwtAuthMiddleware
{
    public function __construct(
        protected JwtServiceInterface $jwtService,
        protected BlacklistServiceInterface $blacklistService
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Получаем токен из заголовка Authorization
            $token = $this->extractTokenFromRequest($request);
            
            if (!$token) {
                return response()->json([
                    'error' => 'Token not provided',
                    'message' => 'Authorization token is required'
                ], 401);
            }

            // Проверяем, не находится ли токен в blacklist
            if ($this->blacklistService->isBlacklisted($token)) {
                return response()->json([
                    'error' => 'Token revoked',
                    'message' => 'Token has been revoked'
                ], 401);
            }

            // Валидируем токен
            $payload = $this->jwtService->validateToken($token);
            if (!$payload) {
                return response()->json([
                    'error' => 'Invalid token',
                    'message' => 'Token is invalid or expired'
                ], 401);
            }

            // Получаем пользователя из токена
            $user = $this->jwtService->getUserFromToken($token);
            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'User associated with token not found'
                ], 401);
            }

            // Устанавливаем пользователя в request для дальнейшего использования
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // Добавляем токен в request
            $request->merge(['jwt_token' => $token]);

            return $next($request);

        } catch (\Throwable $e) {
            Log::error('JWT Middleware error', [
                'error' => $e->getMessage(),
                'request' => $request->url()
            ]);

            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'An error occurred during authentication'
            ], 401);
        }
    }

    /**
     * Извлекает JWT токен из запроса
     */
    private function extractTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization');
        
        if (!$header) {
            return null;
        }

        // Проверяем формат "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
