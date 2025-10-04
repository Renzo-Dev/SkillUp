<?php

namespace App\DTOs;

class LoginRequestDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    /**
     * Создание DTO из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
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
            email: $request->input('email'),
            password: $request->input('password')
        );
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}