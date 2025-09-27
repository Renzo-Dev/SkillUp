<?php

namespace App\Services\RabbitMQ;

use Illuminate\Contracts\Logging\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventPublisher
{
    public function __construct(private array $config, private Log $logger)
    {
    }

    public function publish(string $routingKey, array $payload): void
    {
        $connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'] ?? '/'
        );

        $channel = $connection->channel();
        $channel->exchange_declare($this->config['exchange'], 'topic', false, true, false);

        $message = new AMQPMessage(json_encode($payload, JSON_THROW_ON_ERROR), [
            'content_type' => 'application/json',
            'delivery_mode' => 2,
        ]);

        $channel->basic_publish($message, $this->config['exchange'], $routingKey);
        $channel->close();
        $connection->close();
    }
}

