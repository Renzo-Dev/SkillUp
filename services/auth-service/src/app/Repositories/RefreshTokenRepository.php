<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserRefreshToken;
use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
  public function create(array $data): ?UserRefreshToken
  {
    try {
      return UserRefreshToken::create($data);
    } catch (\Exception $e) {
      Log::error('Error creating refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function findValidToken(string $refreshToken): ?UserRefreshToken{
    try {
      return UserRefreshToken::where('token', $refreshToken)->where('expires_at', '>', now())->first();
    } catch (\Exception $e) {
      Log::error('Error finding valid refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function findByToken(string $refreshToken): ?UserRefreshToken{
    try {
      return UserRefreshToken::where('token', $refreshToken)->first();
    } catch (\Exception $e) {
      Log::error('Error finding refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function update(UserRefreshToken $token, array $data): ?UserRefreshToken{
    try {
      return $token->update($data);
    } catch (\Exception $e) {
      Log::error('Error updating refresh token: ' . $e->getMessage());
      return null;
    }
  }

  public function delete(UserRefreshToken $token): bool{
    try {
      return $token->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting refresh token: ' . $e->getMessage());
      return false;
    }
  }

  public function getUserActiveTokens(User $user): Collection{
    try {
      return UserRefreshToken::where('user_id', $user->id)->where('expires_at', '>', now())->get();
    } catch (\Exception $e) {
      Log::error('Error getting user active tokens: ' . $e->getMessage());
      return null;
    }
  }

  public function getUserTokens(User $user): Collection{
    try {
      return UserRefreshToken::where('user_id', $user->id)->get();
    } catch (\Exception $e) {
      Log::error('Error getting user tokens: ' . $e->getMessage());
      return null;
    }
  }

  public function deleteAllUserTokens(User $user): bool{
    try {
      return UserRefreshToken::where('user_id', $user->id)->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting all user tokens: ' . $e->getMessage());
      return false;
    }
  }

  public function deleteExpiredTokens(): int{
    try {
      return UserRefreshToken::where('expires_at', '<', now())->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting expired tokens: ' . $e->getMessage());
      return 0;
    }
  }

  public function getOldestUserTokens(User $user, int $limit): Collection{
    try {
      return UserRefreshToken::where('user_id', $user->id)->orderBy('expires_at', 'asc')->limit($limit)->get();
    } catch (\Exception $e) {
      Log::error('Error getting oldest user tokens: ' . $e->getMessage());
      return null;
    }
  }
}