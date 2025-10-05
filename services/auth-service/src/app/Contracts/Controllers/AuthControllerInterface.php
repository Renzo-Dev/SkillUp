<?php

namespace App\Contracts\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;

interface AuthControllerInterface
{
    /**
     * Аутентификация пользователя
     */
    public function login(LoginRequest $request): JsonResponse;

    /**
     * Регистрация нового пользователя
     */
    public function register(RegisterRequest $request): JsonResponse;

    /**
     * Выход из системы
     */
    public function logout(): JsonResponse;

    /**
     * Получение информации о текущем пользователе
     */
    public function me(): JsonResponse;
}
