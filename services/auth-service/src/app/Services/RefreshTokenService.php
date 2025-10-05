<?php

namespace App\Services;

use App\Contracts\Services\RefreshTokenServiceInterface;
use App\Contracts\TokenInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Contracts\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenService implements RefreshTokenServiceInterface, TokenInterface
{
  public function __construct(
    private RefreshTokenRepositoryInterface $refreshTokenRepository
  ) {}

  public function generate(User $user): string
  {
    try {
      // Генерируем уникальный refresh token
      $refreshToken = Str::random(64);
      
      // Время истечения (по умолчанию 7 дней)
      // Получаем срок жизни refresh token из .env (по умолчанию 7 дней)
      $refreshTtl = (int) env('JWT_REFRESH_TTL', 7);
      $expiresAt = now()->addMinutes($refreshTtl);

      $tokenData = [
        'user_id' => $user->id,
        'refresh_token' => $refreshToken,
        'expires_at' => $expiresAt,
      ];
      
      $this->refreshTokenRepository->create($tokenData);

      return $refreshToken;

      // Создаем refresh token в базе данных
    } catch (\Exception $e) {
      Log::error("Ошибка генерации refresh token: " . $e->getMessage());
      throw new \Exception("Не удалось создать refresh token");
    }
  }

  public function findValid(string $refreshToken): ?object
  {
    try {
      return $this->refreshTokenRepository->findValidToken($refreshToken);
    } catch (\Exception $e) {
      Log::error("Ошибка поиска валидного refresh токена: " . $e->getMessage());
      return null;
    }
  }

  public function revokeAllUserTokens(User $user): bool{
    try {
      return $this->refreshTokenRepository->deleteAllUserTokens($user);
    } catch (\Exception $e) {
      Log::error("Ошибка удаления всех refresh токенов пользователя: " . $e->getMessage());
      throw new \Exception("Не удалось удалить все refresh токены пользователя");
    }
  }

  public function cleanupExpiredTokens(): int{
    try {
      return $this->refreshTokenRepository->deleteExpiredTokens();
    } catch (\Exception $e) {
      Log::error("Ошибка удаления истекших refresh токенов: " . $e->getMessage());
      throw new \Exception("Не удалось удалить истекшие refresh токены");
    }
  }

  // Реализация методов из TokenInterface
  public function validate(string $token): array
  {
    try {
      $refreshToken = $this->refreshTokenRepository->findValidToken($token);
      if (!$refreshToken) {
        throw new \Exception('Invalid refresh token');
      }
      
      return [
        'user_id' => $refreshToken->user_id,
        'expires_at' => $refreshToken->expires_at,
        'token' => $token
      ];
    } catch (\Exception $e) {
      Log::error("Ошибка валидации refresh токена: " . $e->getMessage());
      throw new \Exception("Не удалось валидировать refresh токен");
    }
  }

  public function isValid(string $token): bool
  {
    try {
      return $this->refreshTokenRepository->isValidToken($token);
    } catch (\Exception $e) {
      Log::error("Ошибка проверки валидности refresh токена: " . $e->getMessage());
      return false;
    }
  }

  public function revoke(string $token): void
  {
    try {
      $refreshToken = $this->refreshTokenRepository->findByToken($token);
      if ($refreshToken) {
        $this->refreshTokenRepository->delete($refreshToken);
      }
    } catch (\Exception $e) {
      Log::error("Ошибка отзыва refresh токена: " . $e->getMessage());
      throw new \Exception("Не удалось отозвать refresh токен");
    }
  }
}