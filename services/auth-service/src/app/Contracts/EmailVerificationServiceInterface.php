<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\EmailVerification;

interface EmailVerificationServiceInterface
{
    /**
     * Сгенерировать токен подтверждения email и отправить письмо
     */
    public function sendVerificationEmail(User $user, ?string $email = null): EmailVerification;

    /**
     * Проверить токен и подтвердить email
     */
    public function verifyByToken(string $token): bool;

    /**
     * Повторно отправить письмо подтверждения
     */
    public function resend(User $user): EmailVerification;

    /**
     * Удалить истекшие токены
     */
    public function cleanupExpiredTokens(): int;

    /**
     * Проверить истечение токена
     */
    public function isTokenExpired(EmailVerification $verification): bool;
}


