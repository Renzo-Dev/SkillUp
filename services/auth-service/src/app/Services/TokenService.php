<?php

namespace App\Services;

use App\Contracts\Services\TokenServiceInterface;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RefreshTokenService;

class TokenService implements TokenServiceInterface
{
  public function __construct(
    private JwtService $jwtService,
    private RefreshTokenService $refreshTokenService
  ) {}

  public function generateTokenPair(User $user): array {
    return [
      'access_token' => $this->jwtService->generate($user),
      'refresh_token' => $this->refreshTokenService->generate($user),
    ];
  }

  public function refreshTokenPair(string $refreshToken): ?array {
    // TODO: реализовать обновление пары токенов
    return null;
  }

  public function refreshJwtToken(string $refreshToken): ?string {
    // TODO: реализовать обновление JWT токена
    return null;
  }

  public function revokeAllUserTokens(int $userId): void {
    // TODO: реализовать отзыв всех токенов пользователя
  }
}