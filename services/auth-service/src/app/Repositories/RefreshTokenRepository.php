<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\RefreshToken;
use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
  public function create(array $data): ?RefreshToken
  {
    try {
      return RefreshToken::create($data);
    } catch (\Exception $e) {
      Log::error('Error creating refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function findValidToken(string $refreshToken): ?RefreshToken{
    try {
      return RefreshToken::findValidToken($refreshToken);
    } catch (\Exception $e) {
      Log::error('Error finding valid refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function findByToken(string $refreshToken): ?RefreshToken{
    try {
      return RefreshToken::where('refresh_token', $refreshToken)->first();
    } catch (\Exception $e) {
      Log::error('Error finding refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function update(RefreshToken $token, array $data): ?RefreshToken{
    try {
      return $token->update($data);
    } catch (\Exception $e) {
      Log::error('Error updating refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function delete(RefreshToken $token): bool{
    try {
      return $token->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting refresh token: ' . $e->getMessage());
      return false;
    }
  }

  public function getUserActiveTokens(User $user): Collection{
    try {
      return RefreshToken::where('user_id', $user->id)->where('expires_at', '>', now())->get();
    } catch (\Exception $e) {
      Log::error('Error getting user active tokens: ' . $e->getMessage());
      return null;
    }
  }

  public function getUserTokens(User $user): Collection{
    try {
      return RefreshToken::where('user_id', $user->id)->get();
    } catch (\Exception $e) {
      Log::error('Error getting user tokens: ' . $e->getMessage());
      return null;
    }
  }

  public function deleteAllUserTokens(User $user): bool{
    try {
      return RefreshToken::where('user_id', $user->id)->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting all user tokens: ' . $e->getMessage());
      return false;
    }
  }

  public function deleteExpiredTokens(): int{
    try {
      return RefreshToken::where('expires_at', '<', now())->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting expired tokens: ' . $e->getMessage());
      return 0;
    }
  }

  public function getOldestUserTokens(User $user, int $limit): Collection{
    try {
      return RefreshToken::where('user_id', $user->id)->orderBy('expires_at', 'asc')->limit($limit)->get();
    } catch (\Exception $e) {
      Log::error('Error getting oldest user tokens: ' . $e->getMessage());
      return null;
    }
  }

  // Проверка валидности токена
  public function isValidToken(string $refreshToken): bool
  {
    try {
      $token = $this->findValidToken($refreshToken);
      return $token !== null && $token->isValid();
    } catch (\Exception $e) {
      Log::error('Error validating refresh token: ' . $e->getMessage());
      return false;
    }
  }
}