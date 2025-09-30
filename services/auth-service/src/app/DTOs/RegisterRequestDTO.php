<?php

namespace App\DTOs;

class RegisterRequestDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password
    ) {}

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password']
        );
    }

    /**
     * Создание DTO из Request
     */
    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password')
        );
    }

    /**
     * Преобразование в массив для создания пользователя
     */
    public function toUserArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password, // Будет захеширован в сервисе
        ];
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
