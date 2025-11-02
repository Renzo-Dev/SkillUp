<?php

namespace App\Contracts\Services;

use App\Contracts\TokenInterface;

interface JwtServiceInterface extends TokenInterface
{
    /**
     * Декодировать JWT токен и получить payload.
     */
    public function decode(string $token): ?array;

    /**
     * Получить время истечения JWT токена.
     */
    public function getExpirationTime(string $token): ?int;

    /**
     * Проверить, не истёк ли JWT токен.
     */
    public function isExpired(string $token): bool;

    /**
     * Получить публичный RSA ключ для валидации JWT в других сервисах.
     * 
     * @return string Содержимое PEM файла
     * @throws \Exception Если ключ не найден или не читается
     */
    public function getPublicKey(): string;

    /**
     * Получить путь к публичному ключу.
     */
    public function getPublicKeyPath(): string;

    /**
     * Получить алгоритм подписи токена.
     */
    public function getAlgorithm(): string;
}