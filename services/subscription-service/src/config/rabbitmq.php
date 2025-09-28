<?php

return [
    'host' => env('RABBITMQ_HOST', 'rabbitmq'),
    'port' => (int) env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),

    'incoming' => [
        'exchange' => env('RABBITMQ_IN_EXCHANGE', 'payments.events'),
        'queue' => env('RABBITMQ_IN_QUEUE', 'subscription.payments'),
        'routing_keys' => [
            'payment.success',
            'payment.failed',
            'subscription.renewed',
            'subscription.cancelled',
        ],
    ],

    'outgoing' => [
        'exchange' => env('RABBITMQ_OUT_EXCHANGE', 'subscriptions.events'),
        'routing_key_prefix' => env('RABBITMQ_OUT_ROUTING_KEY_PREFIX', 'subscription'),
    ],

    'consumer' => [
        'prefetch_count' => (int) env('RABBITMQ_PREFETCH_COUNT', 10),
        'retry_delay' => (int) env('RABBITMQ_RETRY_DELAY', 15),
        'queue' => env('RABBITMQ_IN_QUEUE', 'subscription.payments'),
    ],
];

