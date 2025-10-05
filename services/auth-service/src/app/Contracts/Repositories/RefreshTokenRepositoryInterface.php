<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Database\Eloquent\Collection;

interface RefreshTokenRepositoryInterface
{
    // Создание refresh токена
    public function create(array $data): ?RefreshToken;
    
    // Поиск валидного токена по строке
    public function findValidToken(string $refreshToken): ?RefreshToken;
    
    // Поиск токена по строке (без проверки валидности)
    public function findByToken(string $refreshToken): ?RefreshToken;
    
    // Обновление токена
    public function update(RefreshToken $token, array $data): ?RefreshToken;
    
    // Удаление токена
    public function delete(RefreshToken $token): bool;
    
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