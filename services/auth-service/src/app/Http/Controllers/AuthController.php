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
            // Создаем DTO из валидированного запроса
            $dto = LoginRequestDTO::fromRequest($request);
            
            // Аутентифицируем пользователя
            $response = $this->authService->login($dto);
            
            if (!$response) {
                return ApiErrorResource::create(
                    'Неверные учетные данные',
                    401
                )->response();
            }
            
            // Возвращаем успешный ответ
            return response()->json([
                'success' => true,
                'message' => 'Вход выполнен успешно',
                'data' => $response->toArray()
            ], 200);
            
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
        try {
            // Получаем токен из заголовка
            $token = $this->extractTokenFromRequest();
            
            if (!$token) {
                return ApiErrorResource::create(
                    'Токен не предоставлен',
                    401
                )->response();
            }
            
            // Выходим из системы
            $success = $this->authService->logout($token);
            
            if (!$success) {
                return ApiErrorResource::create(
                    'Ошибка при выходе из системы',
                    500
                )->response();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ], 200);
            
        } catch (\Exception $e) {
            $this->logger->controllerError($e->getMessage());
            return ApiErrorResource::create(
                'Ошибка при выходе: ' . $e->getMessage(),
                500
            )->response();
        }
    }

    /**
     * Обновление токена
     */
    public function refresh(): JsonResponse
    {
        try {
            // Получаем refresh токен из запроса
            $refreshToken = request()->input('refresh_token');
            
            if (!$refreshToken) {
                return ApiErrorResource::create(
                    'Refresh токен не предоставлен',
                    400
                )->response();
            }
            
            // Обновляем токен
            $response = $this->authService->refreshToken($refreshToken);
            
            if (!$response) {
                return ApiErrorResource::create(
                    'Недействительный refresh токен',
                    401
                )->response();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Токен обновлен успешно',
                'data' => $response->toArray()
            ], 200);
            
        } catch (\Exception $e) {
            $this->logger->controllerError($e->getMessage());
            return ApiErrorResource::create(
                'Ошибка при обновлении токена: ' . $e->getMessage(),
                500
            )->response();
        }
    }

    /**
     * Получение информации о текущем пользователе
     */
    public function me(): JsonResponse
    {
        try {
            // Получаем информацию о текущем пользователе
            $response = $this->authService->me();
            // Возвращаем ответ
            return response()->json($response);
        } catch (\Exception $e) {
            // Логируем ошибку
            $this->logger->controllerError($e->getMessage());
            // Возвращаем ошибку
            return ApiErrorResource::create(
                'Ошибка получения информации о текущем пользователе: ' . $e->getMessage(),
                401
            )->response();
        }
    }

    /**
     * Извлечение токена из заголовка Authorization
     */
    private function extractTokenFromRequest(): ?string
    {
        $authorization = request()->header('Authorization');
        
        if (!$authorization) {
            return null;
        }
        
        // Проверяем формат "Bearer {token}"
        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

}
