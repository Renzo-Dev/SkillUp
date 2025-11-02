<?php

namespace App\DTOs;

use App\Models\User;
use App\Contracts\Services\EmailVerificationServiceInterface;

class AuthResponseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly ?bool $emailVerified = null
    ) {}

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            user: $data['user'],
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'],
            emailVerified: $data['email_verified'] ?? null
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
                'email_verified' => $this->emailVerified,
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