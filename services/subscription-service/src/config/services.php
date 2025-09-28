<?php

return [
    'payment_service' => [
        'base_url' => env('PAYMENT_SERVICE_URL'),
        'timeout' => (int) env('PAYMENT_SERVICE_TIMEOUT', 5),
        'endpoints' => [
            'initiate_payment' => env('PAYMENT_SERVICE_INITIATE_ENDPOINT', '/api/payments'),
            'sync_status' => env('PAYMENT_SERVICE_SYNC_ENDPOINT', '/api/payments/{id}/status'),
        ],
    ],

    'auth_service' => [
        'jwks_url' => env('AUTH_JWKS_URL'),
        'timeout' => (int) env('AUTH_JWKS_TIMEOUT', 3),
    ],

    'notification_service' => [
        'base_url' => env('NOTIFICATION_SERVICE_URL'),
        'timeout' => (int) env('NOTIFICATION_SERVICE_TIMEOUT', 5),
    ],
];
