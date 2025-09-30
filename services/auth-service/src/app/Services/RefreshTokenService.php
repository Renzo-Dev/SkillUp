<?php

namespace App\Services;

use App\Contracts\RefreshTokenServiceInterface;
use App\Models\User;
use App\Models\UserRefreshToken;

class RefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct()
    {
    }

    public function createRefreshToken(User $user, string $deviceInfo = null, string $ipAddress = null): UserRefreshToken
    {
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
        // TODO: реализовать отзыв токена
    }

    public function revokeTokenByString(string $refreshToken): bool
    {
        // TODO: реализовать отзыв токена по строке
    }

    public function revokeAllUserTokens(User $user): void
    {
        // TODO: реализовать отзыв всех токенов пользователя
    }

    public function isTokenValid(UserRefreshToken $token): bool
    {
        // TODO: реализовать проверку валидности токена
    }

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

    public function getUserActiveTokens(User $user): \Illuminate\Database\Eloquent\Collection
    {
        // TODO: реализовать получение активных токенов пользователя
    }

    public function limitUserSessions(User $user, int $maxSessions = 5): void
    {
        // TODO: реализовать ограничение количества активных сессий
    }
}