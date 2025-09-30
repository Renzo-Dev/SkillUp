<?php

namespace App\Services;

use App\Contracts\BlacklistServiceInterface;
use Illuminate\Support\Facades\Redis;

class BlacklistService implements BlacklistServiceInterface
{
    protected string $prefix = 'jwt_blacklist:';
    protected int $ttl; // ttl в секундах

    // Инициализируем ttl в конструкторе
    public function __construct()
    {
        $this->ttl = config('jwt.ttl', 60) * 60;
    }

    // Добавляем токен в blacklist с TTL
    public function addToken(string $token): bool
    {
        $key = $this->prefix . $token;
        $result = Redis::setex($key, $this->ttl, 1);
        return (string)$result === 'OK' || $result === true;
    }

    // Проверяем, есть ли токен в blacklist
    public function isBlacklisted(string $token): bool
    {
        $key = $this->prefix . $token;
        return Redis::exists($key) === 1;
    }
}