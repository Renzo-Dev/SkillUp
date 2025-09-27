<?php

namespace App\Services\RabbitMQ;

use App\Jobs\PaymentEventJob;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PaymentConsumer
{
    public function __construct(private array $config, private Queue $queue)
    {
    }

    public function consume(): void
    {
        $connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'] ?? '/'
        );

        $channel = $connection->channel();
        $channel->queue_declare($this->config['incoming']['queue'], false, true, false, false);
        $channel->basic_qos(null, $this->config['consumer']['prefetch_count'], null);

        $callback = function (AMQPMessage $message) {
            try {
                $payload = json_decode($message->getBody(), true, flags: JSON_THROW_ON_ERROR);
                PaymentEventJob::dispatch($payload);
                $message->ack();
            } catch (\Throwable $throwable) {
                Log::error('payment_consumer_failed', ['error' => $throwable->getMessage()]);
                $message->nack(false, true);
            }
        };

        $channel->basic_consume($this->config['incoming']['queue'], '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

