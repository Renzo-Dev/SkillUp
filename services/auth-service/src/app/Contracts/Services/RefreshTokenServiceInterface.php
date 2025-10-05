<?php

namespace App\Contracts\Services;

use App\Models\User;

interface RefreshTokenServiceInterface
{
    /**
     * Сгенерировать refresh токен для пользователя.
     */
    public function generate(User $user): string;

    /**
     * Проверить валидность refresh токена.
     */
    public function isValid(string $refreshToken): bool;

    /**
     * Найти валидный refresh токен.
     */
    public function findValid(string $refreshToken): ?object;

    /**
     * Отозвать все refresh токены пользователя.
     */
    public function revokeAllUserTokens(User $user): bool;

    /**
     * Очистить истёкшие refresh токены.
     */
    public function cleanupExpiredTokens(): int;
}