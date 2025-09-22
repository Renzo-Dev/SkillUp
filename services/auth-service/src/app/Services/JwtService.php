<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class JwtService
{
  public function generateAccessToken(User $user): string
  {
    $customClaims = [
      'role' => $user->role,
      'is_active' => $user->is_active,
      'exp' => now()->addMinutes(config('jwt.ttl', 15))->timestamp,
    ];

    return JWTAuth::customClaims($customClaims)->fromUser($user);
  }

  public function generateRefreshToken(User $user): string
  {
    $token = Str::random(64);
    $tokenHash = hash('sha256', $token);
    $expiresAt = now()->addDays(7);

    // Удаляем старые refresh токены пользователя
    RefreshToken::where('user_id', $user->id)->delete();

    // Создаем новый refresh токен
    RefreshToken::create([
      'user_id' => $user->id,
      'token_hash' => $tokenHash,
      'expires_at' => $expiresAt,
    ]);

    return $token;
  }

  public function validateRefreshToken(string $refreshToken): ?User
  {
    $tokenHash = hash('sha256', $refreshToken);

    $refreshTokenModel = RefreshToken::where('token_hash', $tokenHash)
      ->where('expires_at', '>', now())
      ->first();

    if (!$refreshTokenModel) {
      return null;
    }

    return $refreshTokenModel->user;
  }

  public function revokeRefreshToken(string $refreshToken): void
  {
    $tokenHash = hash('sha256', $refreshToken);
    RefreshToken::where('token_hash', $tokenHash)->delete();
  }

  public function revokeAllUserTokens(User $user): void
  {
    RefreshToken::where('user_id', $user->id)->delete();
  }

  public function getTokenPayload(string $token): array
  {
    try {
      return JWTAuth::setToken($token)->getPayload()->toArray();
    } catch (\Exception $e) {
      return [];
    }
  }

  public function isTokenExpired(string $token): bool
  {
    try {
      $payload = JWTAuth::setToken($token)->getPayload();
      return $payload->get('exp') < now()->timestamp;
    } catch (\Exception $e) {
      return true;
    }
  }

  public function getTokenExpiration(): Carbon
  {
    return now()->addMinutes(config('jwt.ttl', 15));
  }

  public function getRefreshTokenExpiration(): Carbon
  {
    return now()->addDays(7);
  }
}
