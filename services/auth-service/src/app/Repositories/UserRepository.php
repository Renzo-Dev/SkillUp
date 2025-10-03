<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private User $user)
    {
    }

    // Создание пользователя
    public function create(array $data): ?User
    {
        return $this->user->create($data);
    }

    // Поиск пользователя по ID
    public function findById(int $id): ?User
    {
        return $this->user->find($id);
    }

    // Поиск пользователя по email
    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    // Обновление пользователя
    public function update(User $user, array $data): ?User
    {
        $user->update($data);
        return $user;
    }

    // Удаление пользователя
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    // Активация пользователя
    public function activate(User $user): ?User
    {
        $user->is_active = true;
        $user->save();
        return $user;
    }

    // Деактивация пользователя
    public function deactivate(User $user): ?User
    {
        $user->is_active = false;
        $user->save();
        return $user;
    }

    // Обновление времени последнего входа
    public function updateLastLogin(User $user): ?User
    {
        $user->last_login_at = now();
        $user->save();
        return $user;
    }

    // Получение всех пользователей с пагинацией
    public function getAllPaginated(int $perPage = 15): mixed
    {
        return $this->user->paginate($perPage);
    }
}
