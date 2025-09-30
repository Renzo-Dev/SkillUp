<?php

namespace App\Services;

use App\Contracts\RefreshTokenServiceInterface;
use App\Models\User;
use App\Models\UserRefreshToken;
use Illuminate\Support\Facades\Log;

class RefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct()
    {
    }

    public function createRefreshToken(User $user, string $deviceInfo = null, string $ipAddress = null): UserRefreshToken
    {
        // Ограничиваем количество активных сессий перед созданием нового токена
        $this->limitUserSessions($user);
        
        // Реализация создания refresh токена
        $refreshToken = \Str::random(64);
        $expiresAt = now()->addMinutes(config('jwt.refresh_ttl'));
        return UserRefreshToken::create([
            'user_id' => $user->id,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidToken(string $refreshToken): ?UserRefreshToken
    {
        try {
            // Просто возвращаем токен, если он валиден (не истёк)
            $token = UserRefreshToken::where('refresh_token', $refreshToken)
                ->where('expires_at', '>', now())
                ->first();
            return $token;
        } catch (\Throwable $e) {
            Log::error('Ошибка поиска валидного токена', [
                'error' => $e->getMessage(),
                'refresh_token' => $refreshToken,
            ]);
            return null;
        }
    }

    public function rotateToken(UserRefreshToken $token): UserRefreshToken
    {
        try {
            $token->refresh_token = \Str::random(64);
            $token->expires_at = now()->addMinutes(config('jwt.refresh_ttl'));
            $token->save();
            return $token;
        } catch (\Throwable $e) {
            Log::error('Ошибка ротации токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return null;
        }
    }

    public function revokeToken(UserRefreshToken $token): void
    {
        try {
            $token->delete();
        } catch (\Throwable $e) {
            Log::error('Ошибка отзыва токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
        }
    }

    // Отзыв токена по строке
    public function revokeTokenByString(string $refreshToken): bool
    {
        try {
            // Проверяем, существует ли токен
            $token = UserRefreshToken::where('refresh_token', $refreshToken)->first();
            // Если токен не найден, возвращаем false
            if (!$token) {
                return false;
            }

            // Отзываем токен
            $this->revokeToken($token);
            return true;
        } catch (\Throwable $e) {
            Log::error('Ошибка отзыва токена по строке', [
                'error' => $e->getMessage(),
                'refresh_token' => $refreshToken,
            ]);
            return false;
        }
    }

    // Отзыв всех токенов пользователя
    public function revokeAllUserTokens(User $user): bool
    {
        try {
            $tokens = UserRefreshToken::where('user_id', $user->id)->get();
            foreach ($tokens as $token) {
                $this->revokeToken($token);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Ошибка отзыва всех токенов пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return false;
        }
    }

    // Проверка валидности токена
    public function isTokenValid(UserRefreshToken $token): bool
    {
        try {
            return $token->expires_at > now();
        } catch (\Throwable $e) {
            Log::error('Ошибка проверки валидности токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return false;
        }
    }

    // Валидация refresh токена по строке (для обновления JWT)
    public function validateRefreshToken(string $refreshToken): bool
    {
        try {
            $token = $this->findValidToken($refreshToken);
            return $token !== null;
        } catch (\Throwable $e) {
            Log::error('Ошибка валидации refresh токена', [
                'error' => $e->getMessage(),
                'refresh_token' => $refreshToken,
            ]);
            return false;
        }
    }

    // Очистка истекших токенов
    public function cleanExpiredTokens(): int
    {
        try {
            return UserRefreshToken::where('expires_at', '<', now())->delete();
        } catch (\Throwable $e) {
            Log::error('Ошибка очистки истекших токенов', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    // Получение активных токенов пользователя
    public function getUserActiveTokens(User $user): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return UserRefreshToken::where('user_id', $user->id)->where('expires_at', '>', now())->get();
        } catch (\Throwable $e) {
            Log::error('Ошибка получения активных токенов пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return collect([]);
        }
    }

    // Ограничение количества активных сессий
    public function limitUserSessions(User $user, int $maxSessions = null): void
    {
        try {
            // Используем значение из конфигурации, если не передано явно
            $maxSessions = $maxSessions ?? config('jwt.max_sessions', 5);
            
            $tokens = $this->getUserActiveTokens($user);
            // Если количество токенов превышает лимит, удаляем самые старые
            if ($tokens->count() >= $maxSessions) {
                $tokensToRemove = $tokens->count() - $maxSessions + 1; // +1 для нового токена
                $oldestTokens = $tokens->sortBy('created_at')->take($tokensToRemove);
                
                foreach ($oldestTokens as $token) {
                    $this->revokeToken($token);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Ошибка ограничения количества активных сессий', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
        }
    }
}