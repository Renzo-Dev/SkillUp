<?php

namespace App\Services;

use App\Contracts\EmailVerificationServiceInterface;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    /**
     * Сгенерировать и отправить письмо с токеном подтверждения
     */
    public function sendVerificationEmail(User $user, ?string $email = null): EmailVerification
    {
        $email = $email ?: $user->email;

        // Удаляем старые неподтверждённые записи для этого email
        EmailVerification::where('user_id', $user->id)
            ->where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        $token = $this->generateToken64UrlSafe(); // 64 символа, URL-safe
        $expiresAt = now()->addHours(24);

        $verification = EmailVerification::create([
            'user_id' => $user->id,
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // Здесь в дальнейшем будет публикация события/сообщения в шину
        // (RabbitMQ/NotificationService) с передачей token и email
        return $verification;
    }

    /**
     * Подтверждение email по токену
     */
    public function verifyByToken(string $token): bool
    {
        $verification = EmailVerification::where('token', $token)->first();
        if (!$verification) {
            return false; // Неверный токен
        }

        if ($this->isTokenExpired($verification)) {
            return false; // Токен истёк
        }

        if ($verification->verified_at) {
            return true; // Уже подтверждено
        }

        $verification->verified_at = now();
        $verification->save();

        // Обновляем пользователя
        $user = $verification->user;
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Чистим другие незавершенные токены для этого email
        EmailVerification::where('user_id', $verification->user_id)
            ->where('email', $verification->email)
            ->whereNull('verified_at')
            ->where('id', '!=', $verification->id)
            ->delete();

        return true;
    }

    /**
     * Повторная отправка письма подтверждения
     */
    public function resend(User $user): EmailVerification
    {
        if ($user->email_verified_at) {
            throw new \RuntimeException('Email уже подтвержден');
        }

        return $this->sendVerificationEmail($user, $user->email);
    }

    /**
     * Очистка истекших токенов
     */
    public function cleanupExpiredTokens(): int
    {
        return EmailVerification::whereNull('verified_at')
            ->where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Проверка истечения токена
     */
    public function isTokenExpired(EmailVerification $verification): bool
    {
        return $verification->expires_at->isPast();
    }

    /**
     * Генерация 64-символьного URL-safe токена (Base64)
     */
    private function generateToken64UrlSafe(): string
    {
        // 48 байт -> base64 = 64 символа без '='; заменяем +/ на -_
        $bytes = random_bytes(48);
        return strtr(base64_encode($bytes), ['+' => '-', '/' => '_']);
    }

    /**
     * Сборка ссылки подтверждения
     */
    // Ссылка не используется в режиме token-only
}


