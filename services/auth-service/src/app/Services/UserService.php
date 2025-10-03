<?php

namespace App\Services;

use App\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Log;
use App\Contracts\UserRepositoryInterface;
use App\Models\User; // Добавил импорт User для строгой типизации

class UserService implements UserServiceInterface
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    // Создание нового пользователя
    public function createUser(array $data): ?User
    {
        try {
            return $this->userRepository->create($data);
        } catch (\Throwable $e) {
            // Логируем ошибку создания пользователя
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
            return $this->userRepository->findById($id);
        } catch (\Throwable $e) {
            // Логируем ошибку поиска по ID
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
            return $this->userRepository->findByEmail($email);
        } catch (\Throwable $e) {
            // Логируем ошибку поиска по email
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
            return $this->userRepository->update($user, $data);
        } catch (\Throwable $e) {
            // Логируем ошибку обновления пользователя
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
            return $this->userRepository->delete($user);
        } catch (\Throwable $e) {
            // Логируем ошибку удаления пользователя
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
            return $this->userRepository->activate($user);
        } catch (\Throwable $e) {
            // Логируем ошибку активации пользователя
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
            return $this->userRepository->deactivate($user);
        } catch (\Throwable $e) {
            // Логируем ошибку деактивации пользователя
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
            return $this->userRepository->updateLastLogin($user);
        } catch (\Throwable $e) {
            // Логируем ошибку обновления времени последнего входа
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
            // Логируем ошибку проверки активности пользователя
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
            // Логируем ошибку получения текущего пользователя
            Log::error('Ошибка получения текущего пользователя', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}