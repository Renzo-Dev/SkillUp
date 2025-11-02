<?php

namespace App\Services;

use App\Contracts\Services\JwtServiceInterface;
use App\Support\JWT\JwtManager;
use App\Support\JWT\JwtPayloadFactory;
use App\Contracts\Services\BlackListServiceInterface;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class JwtService implements JwtServiceInterface
{
  public function __construct(
    private JwtManager $jwtManager,
    private JwtPayloadFactory $payloadFactory,
    private BlackListServiceInterface $blackListService,
  ) {}

  public function generate(User $user): string
  {
    try {
       // Создаём payload через фабрику
       $payload = $this->payloadFactory->make($user);
       
       // Генерируем токен через JwtManager
       return $this->jwtManager->encode($payload);
    } catch (\Exception $e) {
      Log::error('Failed to generate token: ' . $e->getMessage());
      throw new \Exception('Failed to generate token: ' . $e->getMessage());
    }
  }

  public function validate(string $token): array {
    try {
      // Декодируем токен через JwtManager
      return $this->jwtManager->decode($token);
    } catch (\Exception $e) {
      Log::error('Failed to validate token: ' . $e->getMessage());
      throw new \Exception('Failed to validate token: ' . $e->getMessage());
    }
  }

  public function revoke(string $token): void {
    // Добавляем токен в blacklist через BlackListService
    try {
      $this->blackListService->addTokenToBlackList($token);
    } catch (\Exception $e) {
      Log::error('Не удалось отозвать токен: ' . $e->getMessage());
      throw new \Exception('Не удалось отозвать токен: ' . $e->getMessage());
    }
  }

  // Реализация методов из TokenInterface
  public function isValid(string $token): bool
  {
    try {
      // Валидируем токен через JwtManager
      return $this->jwtManager->validate($token);
    } catch (\Exception $e) {
      Log::error('JWT token is invalid: ' . $e->getMessage());
      return false;
    }
  }

  // Реализация методов из JwtServiceInterface
  public function decode(string $token): ?array
  {
    try {
      // Декодируем токен через JwtManager
      return $this->jwtManager->decode($token);
    } catch (\Exception $e) {
      Log::error('Failed to decode JWT token: ' . $e->getMessage());
      return null;
    }
  }

  public function getExpirationTime(string $token): ?int
  {
    try {
      // Получаем exp через JwtManager
      return $this->jwtManager->getExpirationTime($token);
    } catch (\Exception $e) {
      Log::error('Failed to get JWT expiration time: ' . $e->getMessage());
      return null;
    }
  }

  public function isExpired(string $token): bool
  {
    try {
      // Проверяем истечение через JwtManager
      return $this->jwtManager->isExpired($token);
    } catch (\Exception $e) {
      Log::error('Failed to check JWT expiration: ' . $e->getMessage());
      return true;
    }
  }

  /**
   * Получить публичный RSA ключ для валидации JWT в других сервисах
   * 
   * @return string Содержимое PEM файла
   * @throws \Exception Если ключ не найден или не читается
   */
  public function getPublicKey(): string
  {
    try {
      // Получаем публичный ключ из JwtManager
      return $this->jwtManager->getPublicKey();
    } catch (\Exception $e) {
      Log::error('Failed to get public key', ['error' => $e->getMessage()]);
      throw new \Exception('Не удалось получить публичный ключ: ' . $e->getMessage());
    }
  }

  /**
   * Получить путь к публичному ключу
   * 
   * @return string
   */
  public function getPublicKeyPath(): string
  {
    $publicKeyPath = config('jwt.keys.public');
    return str_replace('file://', '', $publicKeyPath);
  }

  /**
   * Получить алгоритм подписи токена
   */
  public function getAlgorithm(): string
  {
    return $this->jwtManager->getAlgorithm();
  }
}