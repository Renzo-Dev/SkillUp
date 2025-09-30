<?php

namespace App\Contracts;

use App\DTOs\AuthResponseDTO;
use App\DTOs\LoginRequestDTO;
use App\DTOs\RegisterRequestDTO;

interface AuthServiceInterface
{
    /**
     * Регистрация нового пользователя
     */
    public function register(RegisterRequestDTO $dto): ?AuthResponseDTO;

    /**
     * Вход пользователя
     */
    public function login(LoginRequestDTO $dto): ?AuthResponseDTO;

    /**
     * Выход пользователя
     */
    public function logout(string $token): bool;
}
