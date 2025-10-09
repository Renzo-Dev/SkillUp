<?php

namespace App\Contracts\Services;

use App\Models\User;

interface JwtMetadataCacheServiceInterface
{
    /**
     * Сохранить метаданные JWT в кеш, используя данные payload.
     */
    public function remember(User $user, array $payload, array $context = []): array;

    /**
     * Извлечь метаданные по jti токена.
     */
    public function get(string $jti): ?array;

    /**
     * Удалить метаданные по jti.
     */
    public function forget(string $jti): void;

    /**
     * Удалить метаданные по строке токена.
     */
    public function forgetByToken(string $token): void;

    /**
     * Сохранить метаданные для конкретного access токена.
     */
    public function rememberFromToken(string $token, User $user, array $context = []): array;
}

