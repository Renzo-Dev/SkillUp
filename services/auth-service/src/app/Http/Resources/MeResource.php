<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\DTOs\UserDTO;

class MeResource extends JsonResource
{
    /**
     * Преобразование данных текущего пользователя в массив для ответа
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [
                'success' => false,
                'message' => 'Пользователь не аутентифицирован',
                'errors' => [
                    'auth' => ['Требуется авторизация']
                ]
            ];
        }

        if ($this->resource instanceof UserDTO) {
            return [
                'success' => true,
                'message' => 'Текущий пользователь',
                'data' => $this->resource->toArray(),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'version' => '1.0'
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Текущий пользователь',
            'data' => $this->resource,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ];
    }
}


