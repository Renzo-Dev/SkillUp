<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
  private AMQPStreamConnection $connection;
  private $channel;

  public function __construct()
  {
    $this->connection = new AMQPStreamConnection(
      config('rabbitmq.host', 'localhost'),
      config('rabbitmq.port', 5672),
      config('rabbitmq.user', 'guest'),
      config('rabbitmq.password', 'guest'),
      config('rabbitmq.vhost', '/')
    );

    $this->channel = $this->connection->channel();
  }

  public function publish(string $event, array $data): void
  {
    try {
      $message = new AMQPMessage(
        json_encode($data),
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
      );

      $this->channel->basic_publish($message, 'auth.events', $event);

      Log::info('Event published', ['event' => $event, 'data' => $data]);
    } catch (\Exception $e) {
      Log::error('Failed to publish event', [
        'event' => $event,
        'data' => $data,
        'error' => $e->getMessage()
      ]);
    }
  }

  public function consume(string $queue, callable $callback): void
  {
    $this->channel->queue_declare($queue, false, true, false, false);
    $this->channel->basic_consume($queue, '', false, false, false, false, $callback);

    while ($this->channel->is_consuming()) {
      $this->channel->wait();
    }
  }

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
