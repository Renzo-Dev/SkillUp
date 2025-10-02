<?php

namespace App\DTOs;

class TokenPairDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken
    ) {}

    /**
     * Создание DTO из объекта
     */
    public static function fromObject(object $tokenPair): self
    {
        return new self(
            accessToken: $tokenPair->accessToken,
            refreshToken: $tokenPair->refreshToken
        );
    }

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token']
        );
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
        ];
    }

    /**
     * Преобразование в JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
