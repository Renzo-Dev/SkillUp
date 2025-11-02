<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;


class UserRepository implements UserRepositoryInterface
{
    // Создание пользователя
    public function create(array $data): ?User
    {
        try {
            return User::create($data);
        } catch (\Exception $e) {
            Log::error('Ошибка при создании пользователя: ' . $e->getMessage());
            return null;
        }
    }

    // Поиск пользователя по ID
    public function findById(int $id): ?User
    {
        try {
            return User::find($id);
        } catch (\Exception $e) {
            Log::error('Ошибка при поиске пользователя по ID: ' . $e->getMessage());
            return null;
        }
    }

    // Поиск пользователя по email
    public function findByEmail(string $email): ?User
    {
        try {
            return User::where('email', $email)->first();
        } catch (\Exception $e) {
            Log::error('Ошибка при поиске пользователя по email: ' . $e->getMessage());
            return null;
        }
    }

    // Обновление пользователя
    public function update(User $user, array $data): ?User
    {
        try {
            $user->update($data);
            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении пользователя: ' . $e->getMessage());
            return null;
        }
    }

    // Удаление пользователя
    public function delete(User $user): bool
    {
        try {
            return (bool) $user->delete();
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении пользователя: ' . $e->getMessage());
            return false;
        }
    }

    // Удаление пользователя по ID
    public function deleteById(int $id): bool
    {
        try {
            // Возвращаем true, если удалено хотя бы 1, иначе false
            return User::where('id', $id)->delete() > 0;
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении пользователя по ID: ' . $e->getMessage());
            return false;
        }
    }

    // Активация пользователя
    public function activate(User $user): ?User
    {
        try {
            $user->update(['is_active' => true]);
            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('Ошибка при активации пользователя: ' . $e->getMessage());
            return null;
        }
    }

    // Деактивация пользователя
    public function deactivate(User $user): ?User
    {
        try {
            $user->update(['is_active' => false]);
            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('Ошибка при деактивации пользователя: ' . $e->getMessage());
            return null;
        }
    }

    // Обновление времени последнего входа пользователя
    public function updateLastLogin(User $user): ?User
    {
        try {
            $user->update(['last_login_at' => now()]);
            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении времени последнего входа: ' . $e->getMessage());
            return null;
        }
    }
}