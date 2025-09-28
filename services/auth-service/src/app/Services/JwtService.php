<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JwtService
{
  /**
   * Генерация access токена для пользователя
   */
  public function generateAccessToken(User $user): string
  {
    return JWTAuth::fromUser($user);
  }

  /**
   * Генерация refresh токена с сохранением в БД
   */
  public function generateRefreshToken(User $user): string
  {
    $token = Str::random(64);
    $expiresAt = now()->addDays(30); // Refresh токен действует 30 дней

    RefreshToken::create([
      'user_id' => $user->id,
      'token_hash' => hash('sha256', $token),
      'expires_at' => $expiresAt,
    ]);

    return $token;
  }

  /**
   * Валидация refresh токена
   */
  public function validateRefreshToken(string $token): ?User
  {
    $refreshToken = RefreshToken::where('token_hash', hash('sha256', $token))
      ->where('expires_at', '>', now())
      ->first();

    if (!$refreshToken) {
      return null;
    }

    return $refreshToken->user;
  }

  /**
   * Отзыв конкретного refresh токена
   */
  public function revokeRefreshToken(string $token): void
  {
    RefreshToken::where('token_hash', hash('sha256', $token))->delete();
  }

  /**
   * Отзыв всех токенов пользователя
   */
  public function revokeAllUserTokens(User $user): void
  {
    // Отзываем все refresh токены
    RefreshToken::where('user_id', $user->id)->delete();
    
    // Отзываем access токен через blacklist
    try {
      JWTAuth::invalidate(JWTAuth::getToken());
    } catch (JWTException $e) {
      // Токен уже недействителен или отсутствует
    }
  }

  /**
   * Очистка истекших refresh токенов
   */
  public function cleanupExpiredTokens(): void
  {
    RefreshToken::where('expires_at', '<', now())->delete();
  }

  /**
   * Получение пользователя из access токена
   */
  public function getUserFromToken(): ?User
  {
    try {
      return JWTAuth::parseToken()->authenticate();
    } catch (JWTException $e) {
      return null;
    }
  }
}
