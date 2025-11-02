<?php

namespace App\Services;

use App\Contracts\Services\BlackListServiceInterface;
use App\Contracts\Services\CustomLoggerInterface;
use Illuminate\Support\Facades\Redis;

class BlackListService implements BlackListServiceInterface
{
    public function __construct(
        private CustomLoggerInterface $logger,
    ) {}

    public function addTokenToBlackList(string $token): bool
    {
    try {

        $key = 'blacklist:tokens';

        $ttl = (int) env('JWT_BLACKLIST_TTL', 60 * 60 * 24);

        Redis::setex("{$key}:{$token}", $ttl, 1);

        return true;
    } catch (\Exception $e) {
        $this->logger->serviceError('Ошибка при добавлении токена в blacklist: ' . $e->getMessage());
        return false;
    }
  }

    public function checkTokenInBlackList(string $token): bool
    {
      try {
      // Проверяем наличие токена в Redis blacklist
      $key = 'blacklist:tokens';
      $exists = Redis::exists("{$key}:{$token}");
      return $exists === 1; // true если токен найден в blacklist
      } catch (\Exception $e) {
        $this->logger->serviceError('Ошибка при проверке токена в blacklist: ' . $e->getMessage());
        return false;
      }
    }
}