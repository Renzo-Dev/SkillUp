<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Минимальная длина пароля
        if (strlen($value) < 6) {
            $fail('Пароль должен содержать минимум 6 символов.');
            return;
        }

        // Проверка на наличие заглавных букв
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Пароль должен содержать хотя бы одну заглавную букву.');
            return;
        }

        // Проверка на наличие строчных букв
        if (!preg_match('/[a-z]/', $value)) {
            $fail('Пароль должен содержать хотя бы одну строчную букву.');
            return;
        }

        // Проверка на наличие цифр
        if (!preg_match('/[0-9]/', $value)) {
            $fail('Пароль должен содержать хотя бы одну цифру.');
            return;
        }

        // Проверка на наличие специальных символов
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('Пароль должен содержать хотя бы один специальный символ.');
        }
    }
}
