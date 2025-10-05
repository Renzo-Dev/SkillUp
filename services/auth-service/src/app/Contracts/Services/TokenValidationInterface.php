<?php

namespace App\Contracts\Services;

interface TokenValidationInterface
{
    /**
     * Проверить валидность refresh токена.
     */
    public function isValidRefreshToken(string $refreshToken): bool;

    /**
     * Найти валидный refresh токен.
     */
    public function findValidRefreshToken(string $refreshToken): ?object;

    /**
     * Очистить истёкшие токены.
     */
    public function cleanupExpiredTokens(): int;
}
