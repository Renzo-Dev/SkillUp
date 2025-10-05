<?php

namespace App\Contracts\Services;

use App\Models\User;

interface TokenServiceInterface
{
    public function generateTokenPair(User $user): array;

    public function refreshTokenPair(string $refreshToken): ?array;

    public function refreshJwtToken(string $refreshToken): ?string;

    public function revokeAllUserTokens(int $userId): void;
}