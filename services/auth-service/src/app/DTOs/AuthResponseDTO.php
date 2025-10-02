<?php

namespace App\DTOs;

use App\Models\User;

class AuthResponseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly string $accessToken,
        public readonly string $refreshToken
    ) {}

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            user: $data['user'],
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'is_active' => $this->user->is_active,
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at,
            ],
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
