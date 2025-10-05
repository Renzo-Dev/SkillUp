<?php

namespace App\Contracts\Services;

use App\DTOs\RegisterRequestDTO;
use App\DTOs\AuthResponseDTO;
use App\DTOs\LoginRequestDTO;
use App\DTOs\UserDTO;
use Illuminate\Http\JsonResponse;

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

    /**
     * Текущий пользователь
     */
    public function me(): ?UserDTO;

    /**
     * Обновление токена
     */
    public function refreshToken(string $token): ?AuthResponseDTO;

    /**
     * Удаление токена
     */
    public function revokeToken(string $token): bool;
}