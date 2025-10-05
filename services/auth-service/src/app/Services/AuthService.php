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

// Пустой сервис авторизации, реализует интерфейс AuthServiceInterface
class AuthService implements AuthServiceInterface
{
  public function __construct(
    private CustomLoggerInterface $logger,
    private UserServiceInterface $userService,
    private AuthEventPublisher $eventPublisher,
    private TokenServiceInterface $tokenService,
  ) {}

    // Регистрация нового пользователя
    public function register(RegisterRequestDTO $dto): ?AuthResponseDTO
    {
      try {
        // Хешируем пароль перед созданием пользователя
        $userData = $dto->toUserArray();
        $userData['password'] = Hash::make($userData['password']);
        
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
      return null;
    }

    // Выход пользователя
    public function logout(string $token): bool
    {
        // TODO: реализовать
    }

    // Текущий пользователь
    public function me(): ?UserDTO
    {
        // TODO: реализовать
    }

    // Обновление токена
    public function refreshToken(string $token): ?AuthResponseDTO
    {
        // TODO: реализовать
    }

    // Удаление токена
    public function revokeToken(string $token): bool
    {
        // TODO: реализовать
    }
}
