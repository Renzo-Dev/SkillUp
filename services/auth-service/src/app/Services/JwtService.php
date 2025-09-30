<?php

namespace App\Services;

use App\Contracts\JwtServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\Models\User;

class JwtService implements JwtServiceInterface
{
    
    public function __construct(
        protected RefreshTokenServiceInterface $refreshTokenService
    ) {
    }

    // Генерация access токена
    public function generateAccessToken(User $user): string
    {
     try {   
        // Start of Selection
        // Генерируем access токен с кастомным payload через Tymon JWT
        // Можно передать дополнительные данные через setCustomClaims
        $customPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'exp' => now()->addMinutes(config('jwt.ttl'))->timestamp,
        ];
        return auth('api')->claims($customPayload)->login($user); // вернёт JWT с кастомным payload
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
            // Проверяем валидность токена через guard
            return auth('api')->validateToken($token);
        } catch (\Throwable $e) {
            Log::error('Ошибка валидации токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            throw new \Exception('Ошибка валидации токена: ' . $e->getMessage());
        }
    }

    // Валидация access токена (для явности, но логика та же)
    public function validateAccessToken(string $token): bool
    {
        // Просто вызываем validateToken и возвращаем true/false
        try {
            return (bool) $this->validateToken($token);
        } catch (\Throwable $e) {
            // Логируем ошибку, возвращаем false
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
            return auth('api')->userFromToken($token);
        } catch (\Throwable $e) {
            Log::error('Ошибка получения пользователя из токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return null;
        }
    }

    // Проверка истечения токена
    public function isTokenExpired(string $token): bool
    {
        try {
            return auth('api')->isTokenExpired($token);
        } catch (\Throwable $e) {
            Log::error('Ошибка проверки истечения токена', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return false;
        }
    }

    // Обновление JWT токенов
    public function refreshTokens(string $refreshToken): ?object
    {
        try {
        $refreshTokenModel = $this->refreshTokenService->findValidToken($refreshToken);
        // Если токен не найден или истёк, возвращаем null
        if (!$refreshTokenModel) {
            return null;
        }

        // Получаем пользователя по токену
        $user = $refreshTokenModel->user;

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

    // Отзыв JWT токена
    public function revokeToken(string $token): void
    {
    /*
        Поэтапный план реализации revokeToken (отзыв JWT токена):
        1. Извлечь user_id из переданного JWT токена (парсим payload).
        2. Найти все refresh токены пользователя, связанные с этим user_id и access токеном (если есть связь).
        3. Отозвать (удалить/деактивировать) refresh токен(ы) пользователя, чтобы access токен стал бесполезен.
        4. (Опционально) Добавить access токен в blacklist (если используется blacklist).
        5. Прологировать действие для аудита.
        6. Не выбрасывать исключения наружу — метод void.
    */
    
    }

    // Отзыв всех JWT токенов пользователя
    public function revokeAllUserTokens(int $userId): void
    {
        // TODO: реализовать
    }
}