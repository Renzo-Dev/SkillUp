<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    // Создание пользователя
    public function create(array $data): ?User;
    
    // Поиск по ID
    public function findById(int $id): ?User;
    
    // Поиск по email
    public function findByEmail(string $email): ?User;
    
    // Обновление пользователя
    public function update(User $user, array $data): ?User;
    
    // Удаление пользователя
    public function delete(User $user): bool;

    // Удаление пользователя по ID
    public function deleteById(int $id): bool;
    
    // Активация пользователя
    public function activate(User $user): ?User;
    
    // Деактивация пользователя
    public function deactivate(User $user): ?User;
    
    // Обновление времени последнего входа
    public function updateLastLogin(User $user): ?User;
}