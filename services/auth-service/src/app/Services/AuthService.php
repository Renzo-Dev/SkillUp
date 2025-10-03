<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\JwtServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\Contracts\BlacklistServiceInterface;
use App\DTOs\AuthResponseDTO;
use App\DTOs\LoginRequestDTO;
use App\DTOs\RegisterRequestDTO;
use App\DTOs\TokenPairDTO;
use App\DTOs\UserDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService implements AuthServiceInterface
{
    // Внедряем зависимости через конструктор
    public function __construct(
        protected JwtServiceInterface $jwtService,
        protected UserServiceInterface $userService,
        protected RefreshTokenServiceInterface $refreshTokenService,
        protected BlacklistServiceInterface $blacklistService
    ) {
    }

    // Регистрация нового пользователя
    public function register(RegisterRequestDTO $dto): ?AuthResponseDTO
    {
        try {
            // Хешируем пароль перед созданием пользователя
            $userData = $dto->toUserArray();
            $userData['password'] = Hash::make($userData['password']);
            
            $user = $this->userService->createUser($userData);

            // Генерируем пару токенов
            $tokenPair = $this->jwtService->generateTokenPair($user);
            $tokenPairDTO = TokenPairDTO::fromObject($tokenPair);

            return new AuthResponseDTO(
                user: $user,
                accessToken: $tokenPairDTO->accessToken,
                refreshToken: $tokenPairDTO->refreshToken
            );
        } catch (\Throwable $e) {
            Log::error('Ошибка регистрации пользователя', [
                'error' => $e->getMessage(),
                'dto' => $dto->toArray()
            ]);
            return null;
        }
    }

    // Вход пользователя
    public function login(LoginRequestDTO $dto): ?AuthResponseDTO
    {
        try {
            // Получаем пользователя по email
            $user = $this->userService->findUserByEmail($dto->email);
            if (!$user) {
                return null;
            }

            // Проверяем пароль
            if (!Hash::check($dto->password, $user->password)) {
                return null;
            }

            // Проверяем активен ли пользователь
            if (!$this->userService->isUserActive($user)) {
                return null;
            }

            // Обновляем дату последнего входа
            $this->userService->updateLastLogin($user);

            // Генерируем токены
            $tokenPair = $this->jwtService->generateTokenPair($user);

            return new AuthResponseDTO(
                user: $user,
                accessToken: $tokenPair->accessToken,
                refreshToken: $tokenPair->refreshToken
            );
        } catch (\Throwable $e) {
            Log::error('Ошибка входа пользователя', [
                'error' => $e->getMessage(),
                'dto' => $dto->toArray()
            ]);
            return null;
        }
    }

    // Выход пользователя (только бизнес-логика)
    public function logout(string $token): bool
    {
        try {
            // Добавляем access токен в blacklist
            $blacklistResult = $this->blacklistService->addToken($token);
            
            // Получаем пользователя из токена для отзыва всех его refresh токенов
            $user = $this->jwtService->getUserFromToken($token);
            if ($user) {
                // Отзываем все refresh токены пользователя
                $this->refreshTokenService->revokeAllUserTokens($user);
            }
            
            // Здесь может быть дополнительная бизнес-логика
            // например, логирование выхода, очистка сессий и т.д.
            return $blacklistResult;
        } catch (\Throwable $e) {
            Log::error('Ошибка выхода пользователя', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            return false;
        }
    }

    // Текущий пользователь
    public function me(): ?UserDTO
    {
        try {
            $user = $this->userService->getCurrentUser();
            if (!$user) {
                return null;
            }
            return UserDTO::fromModel($user);
        } catch (\Throwable $e) {
            Log::error('Ошибка получения текущего пользователя', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
