<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\UserRefreshToken;

interface RefreshTokenServiceInterface
{
    /**
     * Создание нового refresh токена
     */
    public function createRefreshToken(User $user): UserRefreshToken;

    /**
     * Поиск валидного токена
     */
    public function findValidToken(string $refreshToken): ?UserRefreshToken;

    /**
     * Ротация токена (создание нового)
     */
    public function rotateToken(UserRefreshToken $token): UserRefreshToken;

    /**
     * Отзыв токена
     */
    public function revokeToken(UserRefreshToken $token): void;

    /**
     * Отзыв токена по строке
     */
    public function revokeTokenByString(string $refreshToken): bool;

    /**
     * Отзыв всех токенов пользователя
     */
    public function revokeAllUserTokens(User $user): bool;

    /**
     * Проверка валидности токена
     */
    public function isTokenValid(UserRefreshToken $token): bool;

    /**
     * Очистка истекших токенов
     */
    public function cleanExpiredTokens(): int;

    /**
     * Получение активных токенов пользователя
     */
    public function getUserActiveTokens(User $user): \Illuminate\Database\Eloquent\Collection;

    /**
     * Ограничение количества активных сессий
     */
    public function limitUserSessions(User $user, int $maxSessions = 5): void;
}
