<?php

namespace App\Contracts\Services;

use App\Models\User;
use App\Models\EmailVerification;

interface EmailVerificationServiceInterface
{
    /**
     * Создание токена верификации для пользователя
     */
    public function createVerificationToken(User $user, string $email): ?EmailVerification;

    /**
     * Проверка токена верификации
     */
    public function verifyToken(string $token): ?EmailVerification;

    /**
     * Подтверждение email пользователя
     */
    public function confirmEmail(string $token): bool;

    /**
     * Проверка, верифицирован ли email пользователя
     */
    public function isEmailVerified(User $user, string $email): bool;

    /**
     * Получение активного токена верификации для пользователя
     */
    public function getActiveToken(User $user, string $email): ?EmailVerification;

    /**
     * Удаление истекших токенов для пользователя
     */
    public function cleanupExpiredTokens(User $user): int;

    /**
     * Удаление всех токенов для пользователя
     */
    public function revokeAllTokens(User $user): bool;

    /**
     * Проверка валидности токена (не истек)
     */
    public function isTokenValid(EmailVerification $token): bool;

    /**
     * Получение токена по ID
     */
    public function findTokenById(int $id): ?EmailVerification;

    /**
     * Обновление токена (например, продление срока действия)
     */
    public function updateToken(EmailVerification $token, array $data): ?EmailVerification;
}
