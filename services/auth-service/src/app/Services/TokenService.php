<?php

namespace App\Services;

use App\Contracts\Services\TokenServiceInterface;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RefreshTokenService;
use App\Contracts\Services\JwtMetadataCacheServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TokenService implements TokenServiceInterface
{
  public function __construct(
    private JwtService $jwtService,
    private RefreshTokenService $refreshTokenService,
    private JwtMetadataCacheServiceInterface $jwtMetadataCache
  ) {}

  public function generateTokenPair(User $user): array {
    $accessToken = $this->jwtService->generate($user);
    $this->jwtMetadataCache->rememberFromToken($accessToken, $user);

    return [
      'access_token' => $accessToken,
      'refresh_token' => $this->refreshTokenService->generate($user),
    ];
  }

  public function refreshTokenPair(string $refreshToken): ?array {
    try {
      // Находим валидный refresh token
      $tokenData = $this->refreshTokenService->findValid($refreshToken);
      if (!$tokenData) {
        Log::warning('Refresh token not found or invalid', ['token' => substr($refreshToken, 0, 20)]);
        return null;
      }

      // Получаем пользователя
      $user = User::find($tokenData->user_id);
      if (!$user) {
        Log::warning('User not found for refresh token', ['user_id' => $tokenData->user_id]);
        return null;
      }

      // Отзываем старый refresh token
      $this->refreshTokenService->revoke($refreshToken);

      // Генерируем новую пару токенов и возвращаем с объектом пользователя
      $tokens = $this->generateTokenPair($user);
      return [
        'access_token' => $tokens['access_token'],
        'refresh_token' => $tokens['refresh_token'],
        'user' => $user
      ];
    } catch (\Exception $e) {
      Log::error('Failed to refresh token pair: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
      ]);
      return null;
    }
  }

  public function revokeAllUserTokens(int $userId): void {
    try {
      $user = User::find($userId);
      
      if ($user) {
        $this->refreshTokenService->revokeAllUserTokens($user);
      }
    } catch (\Exception $e) {
      Log::error('Failed to revoke all user tokens: ' . $e->getMessage());
    }
  }

  // Добавляем недостающие методы для совместимости с AuthService
  public function refreshToken(string $refreshToken): ?array {
    return $this->refreshTokenPair($refreshToken);
  }

  public function revokeToken(string $token): bool {
    try {
      $this->jwtService->revoke($token);
      return true;
    } catch (\Exception $e) {
      Log::error('Failed to revoke token: ' . $e->getMessage());
      return false;
    }
  }

  public function refreshJwtToken(string $refreshToken): ?string {
    try {
      $tokenPair = $this->refreshTokenPair($refreshToken);
      return $tokenPair ? $tokenPair['access_token'] : null;
    } catch (\Exception $e) {
      Log::error('Failed to refresh JWT token: ' . $e->getMessage());
      return null;
    }
  }
}