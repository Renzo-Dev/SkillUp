<?php

namespace App\Services;

class JwtService{
  public function __construct(){
  }
  
  // Генерация токенов
  public function generateAccessToken(User $user) :string {}
  public function generateRefreshToken(User $user) :string {}
  public function generateTokenPair(User $user) :TokenResponse {}

  // Валидация токенов
  public function validateToken(string $token): ?TokenPayload {}
  public function validateAccessToken(string $token): bool {}
  public function getUserFromToken(string $token): ?User {}
  public function isTokenExpired(string $token): bool {}

  // Обновление токенов
  public function refreshTokens(string $refreshToken): ?TokenResponse {}
  
  // Отзыв токеновуйу
  public function revokeToken(string $token): void {}
  public function revokeAllUserTokens(int $userId): void {}
}