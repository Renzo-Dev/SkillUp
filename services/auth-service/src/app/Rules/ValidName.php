<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidName implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Проверяем минимальную длину
        if (strlen($value) < 2) {
            $fail('Имя должно содержать минимум 2 символа.');
            return;
        }

        // Проверяем максимальную длину
        if (strlen($value) > 255) {
            $fail('Имя не может быть длиннее 255 символов.');
            return;
        }

        // Проверяем, что имя содержит только буквы, пробелы и дефисы
        if (!preg_match('/^[a-zA-Zа-яА-Я\s\-]+$/u', $value)) {
            $fail('Имя может содержать только буквы, пробелы и дефисы.');
            return;
        }

        // Проверяем, что имя не начинается и не заканчивается пробелом
        if (trim($value) !== $value) {
            $fail('Имя не может начинаться или заканчиваться пробелом.');
        }
    }
}