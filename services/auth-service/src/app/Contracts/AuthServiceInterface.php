<?php

namespace App\Contracts;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

interface AuthServiceInterface
{
    /**
     * Регистрация нового пользователя
     */
    public function register(array $data): array|false;

    /**
     * Вход пользователя
     */
    public function login(LoginRequest $request): array|false;

    /**
     * Выход пользователя
     */
    public function logout(): bool;

    /**
     * Обновление токенов
     */
    public function refresh(string $refreshToken): array|false;

    /**
     * Получение текущего пользователя
     */
    public function getCurrentUser(): ?object;

    /**
     * Проверка аутентификации
     */
    public function isAuthenticated(): bool;
}
