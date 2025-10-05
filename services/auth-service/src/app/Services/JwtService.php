<?php

namespace App\Services;

use App\Contracts\Services\JwtServiceInterface;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class JwtService implements JwtServiceInterface
{
  public function generate(User $user): string
  {
    try {
       // Не добавляем кастомные claims — оставляем стандартные (sub, iat, exp, jti)
       // Время жизни контролируется конфигом jwt.ttl
      return JWTAuth::fromUser($user);
    } catch (\Exception $e) {
      Log::error('Failed to generate token: ' . $e->getMessage());
      throw new \Exception('Failed to generate token: ' . $e->getMessage());
    }
  }

  public function validate(string $token): array {
    try {
      return JWTAuth::parseToken()->getPayload()->toArray();
    } catch (\Exception $e) {
      Log::error('Failed to validate token: ' . $e->getMessage());
      throw new \Exception('Failed to validate token: ' . $e->getMessage());
    }
  }

  public function revoke(string $token): void {
    // Добавляем токен в blacklist через JWTAuth (отзыв токена)
    try {
      JWTAuth::setToken($token)->invalidate();
    } catch (\Exception $e) {
      Log::error('Не удалось отозвать токен: ' . $e->getMessage());
      throw new \Exception('Не удалось отозвать токен: ' . $e->getMessage());
    }
  }

  // Реализация методов из TokenInterface
  public function isValid(string $token): bool
  {
    try {
      JWTAuth::parseToken()->getPayload();
      return true;
    } catch (\Exception $e) {
      Log::error('JWT token is invalid: ' . $e->getMessage());
      return false;
    }
  }

  // Реализация методов из JwtServiceInterface
  public function decode(string $token): ?array
  {
    try {
      return JWTAuth::parseToken()->getPayload()->toArray();
    } catch (\Exception $e) {
      Log::error('Failed to decode JWT token: ' . $e->getMessage());
      return null;
    }
  }

  public function getExpirationTime(string $token): ?int
  {
    try {
      $payload = JWTAuth::parseToken()->getPayload();
      return $payload->get('exp');
    } catch (\Exception $e) {
      Log::error('Failed to get JWT expiration time: ' . $e->getMessage());
      return null;
    }
  }

  public function isExpired(string $token): bool
  {
    try {
      $exp = $this->getExpirationTime($token);
      if ($exp === null) {
        return true;
      }
      return $exp < time();
    } catch (\Exception $e) {
      Log::error('Failed to check JWT expiration: ' . $e->getMessage());
      return true;
    }
  }
}