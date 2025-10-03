<?php

namespace App\Repositories;

use App\Contracts\RefreshTokenRepositoryInterface;
use App\Models\User;
use App\Models\UserRefreshToken;
use Illuminate\Database\Eloquent\Collection;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function create(array $data): ?UserRefreshToken
    {
        return UserRefreshToken::create($data);
    }

    public function findValidToken(string $refreshToken): ?UserRefreshToken
    {
        return UserRefreshToken::where('refresh_token', $refreshToken)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findByToken(string $refreshToken): ?UserRefreshToken
    {
        return UserRefreshToken::where('refresh_token', $refreshToken)->first();
    }

    public function update(UserRefreshToken $token, array $data): ?UserRefreshToken
    {
        $token->update($data);
        return $token;
    }

    public function delete(UserRefreshToken $token): bool
    {
        return (bool) $token->delete();
    }

    public function getUserActiveTokens(User $user): Collection
    {
        return UserRefreshToken::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->get();
    }

    public function getUserTokens(User $user): Collection
    {
        return UserRefreshToken::where('user_id', $user->id)->get();
    }

    public function deleteAllUserTokens(User $user): bool
    {
        return (bool) UserRefreshToken::where('user_id', $user->id)->delete();
    }

    public function deleteExpiredTokens(): int
    {
        return UserRefreshToken::where('expires_at', '<', now())->delete();
    }

    public function getOldestUserTokens(User $user, int $limit): Collection
    {
        return UserRefreshToken::where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }
}


