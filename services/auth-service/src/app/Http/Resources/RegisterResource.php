<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\DTOs\AuthResponseDTO;

class RegisterResource extends JsonResource
{
    /**
     * Преобразование данных регистрации в массив для ответа
     */
    public function toArray(Request $request): array
    {
        // Если неудачная попытка регистрации
        if (!$this->resource) {
            return [
                'success' => false,
                'message' => 'Ошибка при регистрации',
                'errors' => [
                    'registration' => ['Не удалось создать пользователя']
                ]
            ];
        }

        // Если это AuthResponseDTO
        if ($this->resource instanceof AuthResponseDTO) {
            return [
                'success' => true,
                'message' => 'Успешная регистрация',
                'data' => $this->resource->toArray(),
            ];
        }

        // Fallback для массива (обратная совместимость)
        return [
            'success' => true,
            'message' => 'Успешная регистрация',
            'data' => $this->resource,
        ];
    }
}