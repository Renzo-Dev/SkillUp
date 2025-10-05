<?php

namespace App\Events;

use App\Contracts\Services\RabbitMQServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для публикации пользовательских событий
 * Auth-Service публикует только БИЗНЕС-СОБЫТИЯ пользователей:
 * - Регистрация, вход, выход
 * 
 * Токены - это внутренняя логика аутентификации, не публикуем!
 */
class AuthEventPublisher
{
    public function __construct(
        private RabbitMQServiceInterface $rabbitMQ
    ) {}

    /**
     * Публикация события регистрации пользователя
     */
    public function publishUserRegistered(array $userData): bool
    {
        $message = [
            'event' => 'user.registered',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => $userData
        ];

        $success = $this->rabbitMQ->publish('user.events', $message);
        
        if ($success) {
            Log::info("Событие user.registered опубликовано", $userData);
        }
        
        return $success;
    }

    /**
     * Публикация события входа пользователя
     */
    public function publishUserLoggedIn(array $userData): bool
    {
        $message = [
            'event' => 'user.logged_in',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => $userData
        ];

        $success = $this->rabbitMQ->publish('user.events', $message);
        
        if ($success) {
            Log::info("Событие user.logged_in опубликовано", $userData);
        }
        
        return $success;
    }

    /**
     * Публикация события выхода пользователя
     */
    public function publishUserLoggedOut(array $userData): bool
    {
        $message = [
            'event' => 'user.logged_out',
            'timestamp' => now()->toISOString(),
            'service' => 'auth-service',
            'data' => $userData
        ];

        $success = $this->rabbitMQ->publish('user.events', $message);
        
        if ($success) {
            Log::info("Событие user.logged_out опубликовано", $userData);
        }
        
        return $success;
    }

}
