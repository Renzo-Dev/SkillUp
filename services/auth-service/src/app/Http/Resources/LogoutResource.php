<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\DTOs\UserDTO;

/**
 * Ресурс для JSON ответа при выходе из системы
 */
class LogoutResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив для JSON ответа
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Формируем ответ в зависимости от результата выхода
        return [
            'success'   => (bool) $this->resource,
            'message'   => $this->resource
                ? 'Успешный выход из системы'
                : 'Ошибка при выходе из системы',
        ];
    }
}