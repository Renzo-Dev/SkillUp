<?php

namespace App\Services;

use App\Contracts\JwtServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\BlacklistServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtService implements JwtServiceInterface
{
    // Добавляем зависимости UserService и BlacklistService
    public function __construct(
        protected RefreshTokenServiceInterface $refreshTokenService,
        protected UserServiceInterface $userService,
        protected BlacklistServiceInterface $blacklistService
    ) {
    }

    // Генерация access токена
    public function generateAccessToken(User $user): string
    {
        try {
            // Генерируем access токен с кастомным payload через Tymon JWT
            $customPayload = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'exp' => now()->addMinutes(config('jwt.ttl'))->timestamp,
            ];
            return JWTAuth::claims($customPayload)->fromUser($user); // вернёт JWT с кастомным payload
        } catch (\Throwable $e) {
            Log::error('Ошибка генерации access токена', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            throw new \Exception('Ошибка генерации access токена: ' . $e->getMessage());
        }
    }

    // Генерация пары токенов (access + refresh)
    public function generateTokenPair(User $user): object
    {
        try {
            return (object) [
                'accessToken' => $this->generateAccessToken($user),
                'refreshToken' => $this->refreshTokenService->createRefreshToken($user),
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка генерации пары токенов', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            throw new \Exception('Ошибка генерации пары токенов: ' . $e->getMessage());
        }
    }

    // Валидация токена (универсальная)
    public function validateToken(string $token): ?object
    {
        try {
            // Проверяем валидность токена и возвращаем payload
            if (JWTAuth::setToken($token)->check()) {
                return JWTAuth::setToken($token)->getPayload();
            }
            return null;
        } catch (\Throwable $e) {
            Log::error('Ошибка валидации токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return null;
        }
    }

    // Валидация access токена (для явности, но логика та же)
    public function validateAccessToken(string $token): bool
    {
        try {
            return (bool) $this->validateToken($token);
        } catch (\Throwable $e) {
            Log::warning('Access токен невалиден', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return false;
        }
    }

    // Получение пользователя из токена
    public function getUserFromToken(string $token): ?User
    {
        try {
            // Получаем id пользователя из токена
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload['id'] ?? null;
            if (!$userId) {
                return null;
            }
            // Используем UserService для поиска пользователя
            return $this->userService->findUser($userId);
        } catch (\Throwable $e) {
            Log::error('Ошибка получения пользователя из токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return null;
        }
    }

    // Проверка истечения токена (без полной валидации)
    public function isTokenExpired(string $token): bool
    {
        try {
            // Проверяем только истечение, без проверки подписи
            $payload = JWTAuth::setToken($token)->getPayload();
            $exp = $payload['exp'] ?? null;
            if (!$exp) {
                return true;
            }
            return now()->timestamp > $exp;
        } catch (\Throwable $e) {
            // Если не можем декодировать - считаем истекшим
            return true;
        }
    }

    // Обновление JWT токенов
    public function refreshTokens(string $refreshToken): ?object
    {
        try {
            // Валидируем refresh токен
            if (!$this->refreshTokenService->validateRefreshToken($refreshToken)) {
                return null;
            }

            $refreshTokenModel = $this->refreshTokenService->findValidToken($refreshToken);
            if (!$refreshTokenModel) {
                return null;
            }

            $user = $refreshTokenModel->user;
            if (!$user) {
                return null;
            }

            // Сгенерируем новую пару токенов
            $tokenPair = $this->generateTokenPair($user);

            // Ротируем refresh токен
            $newRefreshTokenModel = $this->refreshTokenService->rotateToken($refreshTokenModel);

            // Возвращаем новую пару токенов
            return (object)[
                'accessToken' => $tokenPair->accessToken,
                'refreshToken' => $newRefreshTokenModel->refresh_token,
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка обновления JWT токенов', [
                'error' => $e->getMessage(),
                'refresh_token' => $refreshToken,
            ]);
            return null;
        }
    }

    // Отзыв JWT токена (только добавляет в blacklist)
    public function revokeToken(string $token): bool
    {
        try {
            // Добавляем access токен в blacklist
            return $this->blacklistService->addToken($token);
        } catch (\Throwable $e) {
            Log::error('Ошибка отзыва JWT токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return false;
        }
    }
}