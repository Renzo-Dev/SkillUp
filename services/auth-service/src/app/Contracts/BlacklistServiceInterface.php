<?php

namespace App\Contracts;

// Интерфейс для работы с blacklist access токенов
interface BlacklistServiceInterface
{
    // Добавить access токен в blacklist
    public function addToken(string $token): bool;

    // Проверить, находится ли access токен в blacklist
    public function isBlacklisted(string $token): bool;

}
