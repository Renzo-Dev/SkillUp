<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

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
    
    // Активация пользователя
    public function activate(User $user): ?User;
    
    // Деактивация пользователя
    public function deactivate(User $user): ?User;
    
    // Обновление времени последнего входа
    public function updateLastLogin(User $user): ?User;
    
    // Получение всех пользователей с пагинацией
    public function getAllPaginated(int $perPage = 15): mixed;
}