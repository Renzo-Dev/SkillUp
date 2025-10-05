<?php

namespace App\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\DTOs\RegisterRequestDTO;
use App\DTOs\AuthResponseDTO;
use App\DTOs\LoginRequestDTO;
use App\DTOs\UserDTO;
use App\Contracts\Services\CustomLoggerInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Events\AuthEventPublisher;
use Illuminate\Support\Facades\Hash;
use App\Contracts\Services\TokenServiceInterface;
use App\Contracts\Services\EmailVerificationServiceInterface;
use App\Models\User;

// Пустой сервис авторизации, реализует интерфейс AuthServiceInterface
class AuthService implements AuthServiceInterface
{
  public function __construct(
    private CustomLoggerInterface $logger,
    private UserServiceInterface $userService,
    private AuthEventPublisher $eventPublisher,
    private TokenServiceInterface $tokenService,
    private EmailVerificationServiceInterface $emailVerificationService
  ) {}

    // Регистрация нового пользователя
    public function register(RegisterRequestDTO $dto): ?AuthResponseDTO
    {
      try {
        $userData = $dto->toUserArray();
        
        $user = $this->userService->createUser($userData);
        $tokenPair = $this->tokenService->generateTokenPair($user);

        // Отправляем событие регистрации пользователя
        $this->eventPublisher->publishUserRegistered([
          'user_id' => $user->id,
          'email' => $user->email,
          'name' => $user->name,
          'registered_at' => now()->toISOString()
        ]);

        $authResponseDTO = new AuthResponseDTO($user, $tokenPair['access_token'], $tokenPair['refresh_token']);

        return $authResponseDTO;

      } catch (\Exception $e) {
        $this->logger->serviceError('Ошибка при регистрации пользователя: ' . $e->getMessage());
        return null;
      }
    }

    // Вход пользователя
    public function login(LoginRequestDTO $dto): ?AuthResponseDTO
    {
        try {
            // Находим пользователя по email (возвращает UserDTO)
            $userDTO = $this->userService->findUserByEmail($dto->email);
            if (!$userDTO) {
                $this->logger->serviceError('Пользователь не найден: ' . $dto->email);
                return null;
            }

            // Получаем модель пользователя по ID для проверки пароля
            $userModel = User::find($userDTO->id);
            if (!$userModel) {
                $this->logger->serviceError('Модель пользователя не найдена: ' . $dto->email);
                return null;
            }

            // Проверяем пароль
            if (!$this->userService->checkPassword($userModel, $dto->password)) {
                $this->logger->serviceError('Неверный пароль для пользователя: ' . $dto->email);
                return null;
            }

            // Проверяем активность пользователя
            if (!$userDTO->isActive) {
                $this->logger->serviceError('Пользователь деактивирован: ' . $dto->email);
                return null;
            }

            // Генерируем токены
            $tokenPair = $this->tokenService->generateTokenPair($userModel);

            // Обновляем время последнего входа
            $this->userService->updateLastLogin($userModel);

            // Проверяем верификацию email
            $emailVerified = $this->emailVerificationService->isEmailVerified($userModel, $userModel->email);

            // Отправляем событие входа пользователя
            $this->eventPublisher->publishUserLoggedIn([
                'user_id' => $userModel->id,
                'email' => $userModel->email,
                'logged_in_at' => now()->toISOString()
            ]);

            return new AuthResponseDTO($userModel, $tokenPair['access_token'], $tokenPair['refresh_token'], $emailVerified);

        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при входе пользователя: ' . $e->getMessage());
            return null;
        }
    }

    // Выход пользователя
    public function logout(string $token): bool
    {
        try {
            // Отзываем токен через tokenService
            return $this->tokenService->revokeToken($token);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при выходе пользователя: ' . $e->getMessage());
            return false;
        }
    }

    // Текущий пользователь
    public function me(): ?UserDTO
    {
      try {
        // Возвращаем DTO напрямую из текущего пользователя без userService
        $user = auth()->user();
        return $user ? UserDTO::fromModel($user) : null;
      } catch (\Exception $e) {
        $this->logger->serviceError('Ошибка при получении информации о текущем пользователе: ' . $e->getMessage());
        return null;
      }
    }

    // Обновление токена
    public function refreshToken(string $token): ?AuthResponseDTO
    {
        try {
            // Обновляем токен через tokenService
            $tokenPair = $this->tokenService->refreshToken($token);
            if (!$tokenPair) {
                return null;
            }
            
            // Получаем пользователя из токена
            $user = auth()->user();
            if (!$user) {
                return null;
            }
            
            return new AuthResponseDTO($user, $tokenPair['access_token'], $tokenPair['refresh_token']);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при обновлении токена: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Отзыв токена
     */
    public function revokeToken(string $token): bool
    {
        try {
            return $this->tokenService->revokeToken($token);
        } catch (\Exception $e) {
            $this->logger->serviceError('Ошибка при отзыве токена: ' . $e->getMessage());
            return false;
        }
    }

}
