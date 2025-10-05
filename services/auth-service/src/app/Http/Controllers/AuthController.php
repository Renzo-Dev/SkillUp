<?php

namespace App\Http\Controllers;

use App\Contracts\Controllers\AuthControllerInterface;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\DTOs\LoginRequestDTO;
use Illuminate\Http\JsonResponse;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\CustomLoggerInterface;
use App\Contracts\Services\BlackListServiceInterface;
use App\Http\Resources\ApiErrorResource;
use App\Contracts\Services\JwtServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\DTOs\RegisterRequestDTO;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller implements AuthControllerInterface
{

    public function __construct(
        private AuthServiceInterface $authService,
        private CustomLoggerInterface $logger,
        private BlackListServiceInterface $blackListService,
        private JwtServiceInterface $jwtService,
        private UserServiceInterface $userService,
    ) {}

    
    /**
     * Аутентификация пользователя
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {

   
        } catch (\Exception $e) {
            // Логируем ошибку
            $this->logger->controllerError($e->getMessage());
            
            // Возвращаем ошибку
            return ApiErrorResource::create(
                'Ошибка аутентификации: ' . $e->getMessage(),
                401
            )->response();
        }
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Создаем DTO из валидированного запроса
            $dto = RegisterRequestDTO::fromRequest($request);
            // Регистрируем пользователя
            $response = $this->authService->register($dto);
            // Возвращаем ответ
            return response()->json($response);
        } catch (\Exception $e) {
            // Логируем ошибку
            $this->logger->controllerError($e->getMessage());
            
            // Возвращаем ошибку
            return ApiErrorResource::create(
                'Ошибка регистрации: ' . $e->getMessage(),
                401
            )->response();
        }
    }

    /**
     * Выход из системы
     */
    public function logout(): JsonResponse
    {
        // TODO: Реализовать логику выхода
        return response()->json(['message' => 'Logout method not implemented yet']);
    }

    /**
     * Получение информации о текущем пользователе
     */
    public function me(): JsonResponse
    {
        // TODO: Реализовать логику получения данных пользователя
        return response()->json(['message' => 'Me method not implemented yet']);
    }
}
