<?php

namespace App\Contracts;

use App\Models\User;

interface TokenInterface
{
    /**
     * Сгенерировать токен (access или refresh).
     */
    public function generate(User $user): string;

    /**
     * Проверить и расшифровать токен.
     */
    public function validate(string $token): array;

    /**
     * Проверить валидность токена (не истёк ли).
     */
    public function isValid(string $token): bool;

    /**
     * Отозвать/удалить токен (актуально для refresh).
     */
    public function revoke(string $token): void;
}
