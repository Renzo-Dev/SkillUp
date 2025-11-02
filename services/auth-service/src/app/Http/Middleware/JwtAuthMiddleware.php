<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\JWT\JwtManager;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\BlackListServiceInterface;
use App\Exceptions\JWT\TokenExpiredException;
use App\Exceptions\JWT\TokenInvalidException;
use App\Exceptions\JWT\TokenBlacklistedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class JwtAuthMiddleware
{
    public function __construct(
        private JwtManager $jwtManager,
        private UserRepositoryInterface $userRepository,
        private BlackListServiceInterface $blacklist,
    ) {}
    
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $this->extractToken($request);
            
            if (!$token) {
                return $this->unauthorizedResponse('Токен не предоставлен', 'TOKEN_MISSING');
            }

            // Проверка blacklist (до валидации токена)
            if ($this->blacklist->checkTokenInBlackList($token)) {
                Log::warning('JWT токен в blacklist');
                return $this->unauthorizedResponse('Токен отозван', 'TOKEN_BLACKLISTED');
            }

            // Валидация и декодирование токена
            $payload = $this->jwtManager->decode($token);
            $userId = (int) ($payload['sub'] ?? 0);
            
            if ($userId <= 0) {
                return $this->unauthorizedResponse('Недействительный идентификатор пользователя', 'USER_ID_INVALID');
            }

            // Получение пользователя из репозитория
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                return $this->unauthorizedResponse('Пользователь не найден', 'USER_NOT_FOUND');
            }

            if (!$user->is_active) {
                return $this->unauthorizedResponse('Пользователь деактивирован', 'USER_INACTIVE');
            }

            // Устанавливаем пользователя в request и в Laravel auth
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            // Устанавливаем пользователя в Auth фасад для доступа через auth()->user()
            Auth::setUser($user);

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
            Log::warning('JWT токен отозван', ['error' => $e->getMessage()]);
            return $this->unauthorizedResponse('Токен отозван', 'TOKEN_BLACKLISTED');
            
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
