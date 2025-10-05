<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\Services\RabbitMQServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Команда для обработки событий пользователей из RabbitMQ
 */
class ConsumeUserEvents extends Command
{
    protected $signature = 'rabbitmq:consume-user-events';
    protected $description = 'Запуск consumer для обработки событий пользователей';

    public function __construct(
        private RabbitMQServiceInterface $rabbitMQ
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Запуск consumer для событий пользователей...');

        try {
            // Пытаемся запустить consumer
            $this->rabbitMQ->consume('user.events', function ($data) {
                $this->handleUserEvent($data);
            });

            return 0;
        } catch (\Exception $e) {
            $this->error("Ошибка при запуске consumer: " . $e->getMessage());
            Log::error("Ошибка consumer: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Обработка события пользователя
     */
    private function handleUserEvent(array $data): void
    {
        $event = $data['event'] ?? null;
        $userData = $data['data'] ?? [];

        switch ($event) {
            case 'user.registered':
                $this->handleUserRegistered($userData);
                break;
                
            case 'user.logged_in':
                $this->handleUserLoggedIn($userData);
                break;
                
            case 'user.logged_out':
                $this->handleUserLoggedOut($userData);
                break;
                
            default:
                Log::warning("Неизвестное событие пользователя: {$event}");
        }
    }

    /**
     * Обработка регистрации пользователя
     */
    private function handleUserRegistered(array $userData): void
    {
        Log::info("Пользователь зарегистрирован", $userData);
        
        // Здесь можно добавить логику:
        // - Отправка welcome email
        // - Создание профиля
        // - Уведомления администратора
    }

    /**
     * Обработка входа пользователя
     */
    private function handleUserLoggedIn(array $userData): void
    {
        Log::info("Пользователь вошел в систему", $userData);
        
        // Здесь можно добавить логику:
        // - Обновление статистики
        // - Аналитика
    }

    /**
     * Обработка выхода пользователя
     */
    private function handleUserLoggedOut(array $userData): void
    {
        Log::info("Пользователь вышел из системы", $userData);
        
        // Здесь можно добавить логику:
        // - Очистка сессий
        // - Аналитика
    }
}
