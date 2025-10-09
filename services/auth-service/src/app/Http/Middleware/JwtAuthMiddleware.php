<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $this->extractToken($request);
            
            if (!$token) {
                return $this->unauthorizedResponse('Токен не предоставлен', 'TOKEN_MISSING');
            }

            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return $this->unauthorizedResponse('Пользователь не найден', 'USER_NOT_FOUND');
            }

            if (!$user->is_active) {
                return $this->unauthorizedResponse('Пользователь деактивирован', 'USER_INACTIVE');
            }

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            Log::info('JWT аутентификация успешна', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return $next($request);

        } catch (TokenExpiredException $e) {
            Log::warning('JWT токен истек', ['error' => $e->getMessage()]);
            return $this->unauthorizedResponse('Токен истек', 'TOKEN_EXPIRED');
            
        } catch (TokenInvalidException $e) {
            Log::warning('JWT токен недействителен', ['error' => $e->getMessage()]);
            return $this->unauthorizedResponse('Токен недействителен', 'TOKEN_INVALID');
            
        } catch (TokenBlacklistedException $e) {
            Log::warning('JWT токен в blacklist', ['error' => $e->getMessage()]);
            return $this->unauthorizedResponse('Токен отозван', 'TOKEN_BLACKLISTED');
            
        } catch (JWTException $e) {
            Log::error('JWT ошибка', ['error' => $e->getMessage()]);
            return $this->unauthorizedResponse('Ошибка аутентификации', 'JWT_ERROR');
            
        } catch (\Exception $e) {
            Log::error('Неожиданная ошибка в JWT middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Внутренняя ошибка сервера');
        }
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        
        if (!$header) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function unauthorizedResponse(string $message, string $errorCode = 'UNAUTHORIZED'): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode
        ], 401);
    }

    private function serverErrorResponse(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'INTERNAL_ERROR'
        ], 500);
    }
}
