<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Проверяем формат email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail('Некорректный формат email адреса.');
            return;
        }

        // Проверяем длину email
        if (strlen($value) > 255) {
            $fail('Email адрес не может быть длиннее 255 символов.');
            return;
        }

        // Проверяем домен (опционально)
        $domain = substr(strrchr($value, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            $fail('Домен email адреса не существует.');
        }
    }
}
