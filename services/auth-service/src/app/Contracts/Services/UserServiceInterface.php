<?php

namespace App\Contracts\Services;

use App\DTOs\UserDTO;
use App\Models\User;

interface UserServiceInterface
{
    public function createUser(array $data): ?User;
    public function updateUser(User $user, array $data): ?User;
    public function deleteUser(User $user): bool;
    public function deleteUserById(int $id): bool;
    public function activateUser(User $user): ?User;
    public function deactivateUser(User $user): ?User;
    public function findUserByEmail(string $email): ?UserDTO;
    public function checkPassword(User $user, string $password): bool;
    public function isUserActive(User $user): bool;
    public function updateLastLogin(User $user): ?User;
}