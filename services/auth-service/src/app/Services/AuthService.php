<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\JwtServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    // Внедряем зависимости через конструктор
    public function __construct(
        protected JwtServiceInterface $jwtService,
        protected UserServiceInterface $userService,
        protected RefreshTokenServiceInterface $refreshTokenService
    ) {
    }

    // Регистрация нового пользователя
    public function register(array $data): array|false
    {
        try {
            // Хешируем пароль перед созданием пользователя
            $data['password'] = Hash::make($data['password']);
            $user = $this->userService->createUser($data);

            // Генерируем пару токенов
            $tokenPair = $this->jwtService->generateTokenPair($user);

            return [
                'user' => $user,
                'access_token' => $tokenPair->accessToken,
                'refresh_token' => $tokenPair->refreshToken,
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка регистрации пользователя', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    // Вход пользователя
    public function login(LoginRequest $request): array|false
    {
        // Получаем пользователя по email
        $user = $this->userService->findUserByEmail($request->input('email'));
        if (!$user) {
            return false;
        }

        // Проверяем пароль
        if (!Hash::check($request->input('password'), $user->password)) {
            return false;
        }

        // Проверяем активен ли пользователь
        if (!$this->userService->isUserActive($user)) {
            return false;
        }

        // Обновляем дату последнего входа
        $this->userService->updateLastLogin($user);

        // Генерируем токены
        $tokenPair = $this->jwtService->generateTokenPair($user);

        return [
            'user' => $user,
            'access_token' => $tokenPair->accessToken,
            'refresh_token' => $tokenPair->refreshToken,
        ];
    }

    // Выход пользователя
    public function logout(): bool
    {
        try {
            $token = request()->bearerToken();
            if (!$token) {
                return false;
            }
            // Инвалидируем токен
            $this->jwtService->revokeToken($token);
            return true;
        } catch (\Throwable $e) {
            Log::error('Ошибка выхода пользователя', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);
            return false;
        }
    }

    // Обновление токенов по refresh_token
    public function refresh(string $refreshToken): array|false
    {
        try {
            // Проверяем refresh token
            $token = $this->refreshTokenService->findValidToken($refreshToken);
            if (!$token) {
                return false;
            }
            
            $user = $token->user;

            // Генерируем новую пару токенов
            $tokenPair = $this->jwtService->generateTokenPair($user);

            return [
                'user' => $user,
                'access_token' => $tokenPair->accessToken,
                'refresh_token' => $tokenPair->refreshToken,
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка обновления токенов', [
                'error' => $e->getMessage(),
                'refresh_token' => $refreshToken
            ]);
            return false;
        }
    }

    // Получение текущего пользователя
    public function getCurrentUser(): ?object
    {
        return Auth::user();
    }

    // Проверка аутентификации
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }
}
