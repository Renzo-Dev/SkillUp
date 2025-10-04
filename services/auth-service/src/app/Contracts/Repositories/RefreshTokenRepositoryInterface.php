<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\UserRefreshToken;
use Illuminate\Database\Eloquent\Collection;

interface RefreshTokenRepositoryInterface
{
    // Создание refresh токена
    public function create(array $data): ?UserRefreshToken;
    
    // Поиск валидного токена по строке
    public function findValidToken(string $refreshToken): ?UserRefreshToken;
    
    // Поиск токена по строке (без проверки валидности)
    public function findByToken(string $refreshToken): ?UserRefreshToken;
    
    // Обновление токена
    public function update(UserRefreshToken $token, array $data): ?UserRefreshToken;
    
    // Удаление токена
    public function delete(UserRefreshToken $token): bool;
    
    // Получение всех активных токенов пользователя
    public function getUserActiveTokens(User $user): Collection;
    
    // Получение всех токенов пользователя
    public function getUserTokens(User $user): Collection;
    
    // Удаление всех токенов пользователя
    public function deleteAllUserTokens(User $user): bool;
    
    // Удаление истекших токенов
    public function deleteExpiredTokens(): int;
    
    // Получение самых старых токенов пользователя
    public function getOldestUserTokens(User $user, int $limit): Collection;
}