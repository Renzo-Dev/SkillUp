<?php

namespace App\Contracts\Services;

/**
 * Интерфейс сервиса RabbitMQ для публикации сообщений
 * ВНИМАНИЕ: Auth-Service только публикует события, не потребляет их!
 */
interface RabbitMQServiceInterface
{
    /**
     * Публикация сообщения в очередь
     * @param string $queue - имя очереди
     * @param array $message - данные сообщения
     * @return bool
     */
    public function publish(string $queue, array $message): bool;

    /**
     * Запуск consumer для обработки сообщений из очереди
     * ВНИМАНИЕ: Auth-Service НЕ должен потреблять события!
     * Consumer должен быть в других сервисах (Email, Analytics, etc.)
     * @param string $queue - имя очереди
     * @param callable $callback - функция обработки сообщения
     * @return void
     */
    public function consume(string $queue, callable $callback): void;
}
