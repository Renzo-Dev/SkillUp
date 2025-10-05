<?php

namespace App\Services;

use App\Contracts\Services\UserServiceInterface;
use App\Contracts\Services\CustomLoggerInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct(
        private CustomLoggerInterface $logger,
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Создание нового пользователя
     */
    public function createUser(array $data): ?User
    {
        try {
            // Пароль будет автоматически хеширован кастом 'hashed' в модели User
            return $this->userRepository->create($data);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при создании пользователя: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Обновление данных пользователя
     */
    public function updateUser(User $user, array $data): ?User
    {
        try {
            return $this->userRepository->update($user, $data);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при обновлении пользователя: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Удаление пользователя
     */
    public function deleteUser(User $user): bool
    {
        try {
            return $this->userRepository->delete($user);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при удалении пользователя: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удаление пользователя по ID
     */
    public function deleteUserById(int $id): bool
    {
        try {
            return $this->userRepository->deleteById($id);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при удалении пользователя по ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Активация пользователя
     */
    public function activateUser(User $user): ?User
    {
        try {
            // Обновляем статус is_active
            return $this->userRepository->update($user, ['is_active' => true]);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при активации пользователя: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Деактивация пользователя
     */
    public function deactivateUser(User $user): ?User
    {
        try {
            // Обновляем статус is_active
            return $this->userRepository->update($user, ['is_active' => false]);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при деактивации пользователя: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Поиск пользователя по email
     */
    public function findUserByEmail(string $email): ?UserDTO
    {
        try {
            $user = $this->userRepository->findByEmail($email);
            return $user ? UserDTO::fromModel($user) : null;
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при поиске пользователя по email: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Проверка пароля пользователя
     */
    public function checkPassword(User $user, string $password): bool
    {
        try {
            $hashedPassword = $user->getRawOriginal('password');
            return Hash::check($password, $hashedPassword);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при проверке пароля: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверка активности пользователя
     */
    public function isUserActive(User $user): bool
    {
        // Проверяем флаг активности
        return (bool) $user->is_active;
    }

    /**
     * Обновление времени последнего входа пользователя
     */
    public function updateLastLogin(User $user): ?User
    {
        try {
            // Обновляем поле last_login_at
            return $this->userRepository->update($user, ['last_login_at' => now()]);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при обновлении времени последнего входа: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Верификация email пользователя
     */
    public function verifyEmail(User $user): ?User
    {
        try {
            // Обновляем поле email_verified_at
            $updatedUser = $this->userRepository->update($user, ['email_verified_at' => now()]);
            
            if ($updatedUser) {
                $this->logger->serviceInfo("Email подтвержден для пользователя {$user->id}");
            }
            
            return $updatedUser;
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при верификации email: ' . $e->getMessage());
            return null;
        }
    }
}