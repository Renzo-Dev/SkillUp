<?php

namespace App\Contracts;

use App\Models\User;

interface JwtServiceInterface
{
    /**
     * Генерация access токена
     */
    public function generateAccessToken(User $user): string;


    /**
     * Генерация пары токенов (access + refresh)
     */
    public function generateTokenPair(User $user): object;

    /**
     * Валидация токена
     */
    public function validateToken(string $token): ?object;

    /**
     * Валидация access токена
     */
    public function validateAccessToken(string $token): bool;

    /**
     * Получение пользователя из токена
     */
    public function getUserFromToken(string $token): ?User;

    /**
     * Проверка истечения токена
     */
    public function isTokenExpired(string $token): bool;

    /**
     * Обновление токенов
     */
    public function refreshTokens(string $refreshToken): ?object;

    /**
     * Отзыв токена
     */
    public function revokeToken(string $token): bool;
}
