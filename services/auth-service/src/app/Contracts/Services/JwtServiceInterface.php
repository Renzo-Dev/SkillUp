<?php

namespace App\Contracts\Services;

use App\Contracts\TokenInterface;

interface JwtServiceInterface extends TokenInterface
{
    /**
     * Декодировать JWT токен и получить payload.
     */
    public function decode(string $token): ?array;

    /**
     * Получить время истечения JWT токена.
     */
    public function getExpirationTime(string $token): ?int;

    /**
     * Проверить, не истёк ли JWT токен.
     */
    public function isExpired(string $token): bool;
}