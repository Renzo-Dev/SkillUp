<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [
                'success' => false,
                'message' => 'Ошибка при регистрации',
            ];
        }

        return [
            'success' => true,
            'message' => 'Успешная регистрация',
            'user' => [
                'id' => $this->resource['user']['id'],
                'name' => $this->resource['user']['name'],
                'email' => $this->resource['user']['email'],
            ],
            'access_token' => $this->resource['access_token'],
            'refresh_token' => $this->resource['refresh_token'],
        ];
    }
}
