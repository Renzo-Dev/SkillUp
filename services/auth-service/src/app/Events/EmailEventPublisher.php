<?php

namespace App\Events;

use App\Contracts\Services\RabbitMQServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для публикации событий верификации email
 * Auth-Service публикует события верификации email:
 * - Запрос верификации email
 * - Подтверждение email
 */
class EmailEventPublisher
{
    public function __construct(
        private RabbitMQServiceInterface $rabbitMQ
    ) {}

    /**
     * Публикация события запроса верификации email
     */
    public function publishEmailVerificationRequested(User $user, $token): bool
    {
        $message = [
            'event' => 'email.verification.requested',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => [
                'user_id' => $user->id,
                'email' => $token->email,
                'name' => $user->name,
                'verification_token' => $token->token,
                'expires_at' => $token->expires_at->toISOString(),
                'verification_url' => config('app.frontend_url', 'http://localhost:3000') . '/verify-email?token=' . $token->token
            ]
        ];

        $success = $this->rabbitMQ->publish('email.verification', $message);
        
        if ($success) {
            Log::info("Событие email.verification.requested опубликовано", [
                'user_id' => $user->id,
                'email' => $token->email
            ]);
        }
        
        return $success;
    }

    /**
     * Публикация события подтверждения email
     */
    public function publishEmailVerificationCompleted(User $user, $verification): bool
    {
        $message = [
            'event' => 'email.verification.completed',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => [
                'user_id' => $user->id,
                'email' => $verification->email,
                'name' => $user->name,
                'verified_at' => $verification->verified_at->toISOString()
            ]
        ];

        $success = $this->rabbitMQ->publish('email.verification', $message);
        
        if ($success) {
            Log::info("Событие email.verification.completed опубликовано", [
                'user_id' => $user->id,
                'email' => $verification->email
            ]);
        }
        
        return $success;
    }

    /**
     * Публикация события повторной отправки верификации
     */
    public function publishEmailVerificationResent(User $user, $token): bool
    {
        $message = [
            'event' => 'email.verification.resent',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => [
                'user_id' => $user->id,
                'email' => $token->email,
                'name' => $user->name,
                'verification_token' => $token->token,
                'expires_at' => $token->expires_at->toISOString(),
                'verification_url' => config('app.frontend_url', 'http://localhost:3000') . '/verify-email?token=' . $token->token
            ]
        ];

        $success = $this->rabbitMQ->publish('email.verification', $message);
        
        if ($success) {
            Log::info("Событие email.verification.resent опубликовано", [
                'user_id' => $user->id,
                'email' => $token->email
            ]);
        }
        
        return $success;
    }
}
