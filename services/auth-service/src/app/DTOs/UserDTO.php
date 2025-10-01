<?php

namespace App\DTOs;

use App\Models\User;

class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $isActive,
        public readonly ?string $emailVerifiedAt,
        public readonly ?string $lastLoginAt,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    /**
     * Создание DTO из модели User
     */
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isActive: $user->is_active,
            emailVerifiedAt: $user->email_verified_at?->toISOString(),
            lastLoginAt: $user->last_login_at?->toISOString(),
            createdAt: $user->created_at->toISOString(),
            updatedAt: $user->updated_at->toISOString()
        );
    }

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            isActive: $data['is_active'] ?? true,
            emailVerifiedAt: $data['email_verified_at'] ?? null,
            lastLoginAt: $data['last_login_at'] ?? null,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at']
        );
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'email_verified_at' => $this->emailVerifiedAt,
            'last_login_at' => $this->lastLoginAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
