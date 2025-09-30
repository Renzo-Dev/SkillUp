<?php

namespace App\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Создание нового пользователя
     */
    public function createUser(array $data): User;

    /**
     * Поиск пользователя по ID
     */
    public function findUser(int $id): ?User;

    /**
     * Поиск пользователя по email
     */
    public function findUserByEmail(string $email): ?User;

    /**
     * Обновление пользователя
     */
    public function updateUser(User $user, array $data): User;

    /**
     * Удаление пользователя
     */
    public function deleteUser(User $user): bool;

    /**
     * Активация пользователя
     */
    public function activateUser(User $user): User;

    /**
     * Деактивация пользователя
     */
    public function deactivateUser(User $user): User;

    /**
     * Обновление времени последнего входа
     */
    public function updateLastLogin(User $user): User;

    /**
     * Проверка активности пользователя
     */
    public function isUserActive(User $user): bool;
}
