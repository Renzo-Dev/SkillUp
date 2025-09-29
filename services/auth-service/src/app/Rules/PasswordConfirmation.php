<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordConfirmation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    // Проверяем, что поле password_confirmation совпадает с password
    $password = request()->input('password');
    if ($value !== $password) {
        // Если не совпадает — возвращаем ошибку
        $fail('Пароль и подтверждение пароля не совпадают.');
    }
    }
}
