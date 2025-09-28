<?php

return [
    'algo' => env('JWT_ALGO', 'RS256'),
    'audience' => env('JWT_AUDIENCE', 'subscription-service'),
    'required_scope' => env('JWT_REQUIRED_SCOPE', 'user.subscription'),
    'admin_scope' => env('JWT_ADMIN_SCOPE', 'subscription.admin'),
    'issuer' => env('JWT_ISSUER'),
    'leeway' => (int) env('JWT_LEEWAY', 60),

    'public_key' => env('JWT_PUBLIC_KEY'),
    'jwks' => [
        'url' => env('AUTH_JWKS_URL'),
        'cache_ttl' => (int) env('JWT_JWKS_CACHE_TTL', 300),
    ],

    'jti_cache_prefix' => env('JWT_JTI_CACHE_PREFIX', 'subscription:jti'),
    'jti_ttl' => (int) env('JWT_JTI_TTL', 900),
];

