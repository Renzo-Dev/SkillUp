<?php

namespace App\Services;

use App\Contracts\Services\RabbitMQServiceInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQService implements RabbitMQServiceInterface
{
    private ?AMQPStreamConnection $connection = null;
    private $channel = null;

    public function __construct()
    {
        // Ленивая инициализация - подключение только при необходимости
    }

    /**
     * Получение подключения к RabbitMQ (ленивая инициализация)
     */
    private function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null) {
            try {
                $this->connection = new AMQPStreamConnection(
                    host: env('RABBITMQ_HOST', 'localhost'),
                    port: env('RABBITMQ_PORT', 5672),
                    user: env('RABBITMQ_USER', 'guest'),
                    password: env('RABBITMQ_PASSWORD', 'guest'),
                    vhost: env('RABBITMQ_VHOST', '/')
                );
                
                $this->channel = $this->connection->channel();
            } catch (\Exception $e) {
                Log::warning("Не удалось подключиться к RabbitMQ: " . $e->getMessage());
                throw $e;
            }
        }
        
        return $this->connection;
    }

    /**
     * Публикация сообщения в очередь (Publisher)
     */
    public function publish(string $queue, array $message): bool
    {
        try {
            // Получаем подключение (ленивая инициализация)
            $this->getConnection();
            
            // Объявляем очередь (создаем если не существует)
            $this->channel->queue_declare($queue, false, true, false, false);

            // Создаем сообщение
            $msg = new AMQPMessage(
                json_encode($message),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            // Публикуем сообщение
            $this->channel->basic_publish($msg, '', $queue);

            Log::info("Сообщение отправлено в очередь {$queue}", $message);
            return true;

        } catch (\Exception $e) {
            Log::error("Ошибка публикации в RabbitMQ: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Запуск consumer для обработки сообщений (Subscriber)
     * ВНИМАНИЕ: Auth-Service НЕ должен потреблять события!
     * Consumer должен быть в других сервисах (Email, Analytics, etc.)
     */
    public function consume(string $queue, callable $callback): void
    {
        // Auth-Service только публикует события, не потребляет их
        throw new \Exception('Auth-Service не должен потреблять события! Consumer должен быть в других сервисах.');
    }

    /**
     * Закрытие соединения
     */
    public function __destruct()
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
}