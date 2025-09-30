<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\DTOs\AuthResponseDTO;

class LoginResource extends JsonResource
{
    /**
     * Преобразование данных логина в массив для ответа
     */
    public function toArray(Request $request): array
    {
        // Если неудачная попытка логина
        if (!$this->resource) {
            return [
                'success' => false,
                'message' => 'Ошибка авторизации',
                'errors' => [
                    'auth' => ['Неверные учетные данные']
                ]
            ];
        }

        // Если это AuthResponseDTO
        if ($this->resource instanceof AuthResponseDTO) {
            return [
                'success' => true,
                'message' => 'Успешная авторизация',
                'data' => $this->resource->toArray(),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'version' => '1.0'
                ]
            ];
        }

        // Fallback для массива (обратная совместимость)
        return [
            'success' => true,
            'message' => 'Успешная авторизация',
            'data' => $this->resource,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ];
    }
}
