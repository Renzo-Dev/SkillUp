<?php

namespace App\Services;

use App\Models\User;
use App\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Log;

class UserService implements UserServiceInterface {
  public function __construct(){
  }

    // Методы интерфейса UserServiceInterface, пока без реализации

    // Создание нового пользователя
    public function createUser(array $data): ?User
    {
        try {
            return User::create($data);
        } catch (\Exception $e) {
            Log::error('Ошибка создания пользователя', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    // Поиск пользователя по ID
    public function findUser(int $id): ?User
    {
        try {
            return User::find($id);
        } catch (\Throwable $e) {
            Log::error('Ошибка поиска пользователя по ID', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            return null;
        }
    }

    // Поиск пользователя по email
    public function findUserByEmail(string $email): ?User
    {
        try {
            return User::where('email', $email)->first();
        } catch (\Throwable $e) {
            Log::error('Ошибка поиска пользователя по email', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
            return null;
        }
    }

    // Обновление пользователя
    public function updateUser(User $user, array $data): ?User
    {
        try {
            $user->update($data);
            return $user;
        } catch (\Throwable $e) {
            Log::error('Ошибка обновления пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return null;
        }
    }

    // Удаление пользователя
    public function deleteUser(User $user): bool
    {
        try {
            $user->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error('Ошибка удаления пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return false;
        }
    }

    // Активация пользователя
    public function activateUser(User $user): ?User
    {
        try {
            $user->is_active = true;
            $user->save();
            return $user;
        } catch (\Throwable $e) {
            Log::error('Ошибка активации пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return null;
        }
    }

    // Деактивация пользователя
    public function deactivateUser(User $user): ?User
    {
        try {
            $user->is_active = false;
            $user->save();
            return $user;
        } catch (\Throwable $e) {
            Log::error('Ошибка деактивации пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return null;
        }
    }

    // Обновление времени последнего входа
    public function updateLastLogin(User $user): ?User
    {
        try {
            $user->last_login_at = now();
            $user->save();
            return $user;
        } catch (\Throwable $e) {
            Log::error('Ошибка обновления времени последнего входа', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return null;
        }
    }

    // Проверка активности пользователя
    public function isUserActive(User $user): bool
    {
        try {
            return (bool) $user->is_active;
        } catch (\Throwable $e) {
            Log::error('Ошибка проверки активности пользователя', [
                'error' => $e->getMessage(),
                'user' => $user,
            ]);
            return false;
        }
    }

    // Получение текущего аутентифицированного пользователя
    public function getCurrentUser(): ?User
    {
        try {
            // Сначала пробуем получить через стандартный auth
            $user = auth()->user();
            
            // Если не получилось, пробуем через request (для кастомного middleware)
            if (!$user && request()->user()) {
                $user = request()->user();
            }
            
            return $user;
        } catch (\Throwable $e) {
            Log::error('Ошибка получения текущего пользователя', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}