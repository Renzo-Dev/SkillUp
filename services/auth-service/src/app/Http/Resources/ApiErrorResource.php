<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс для JSON ответа при ошибке
 */
class ApiErrorResource extends JsonResource
{
    /**
     * Создание ресурса ошибки
     */
    public static function create(string $message, int $code = 500, array $errors = []): self
    {
        return new self([
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
        ]);
    }

    /**
     * Преобразование ресурса в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => false,
            'message' => $this->resource['message'] ?? 'Unexpected error',
            'code' => $this->resource['code'] ?? 500,
            'errors' => $this->resource['errors'] ?? [],
        ];
    }
}