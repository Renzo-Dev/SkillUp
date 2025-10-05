<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidEmail;
use App\Rules\StrongPassword;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Разрешаем выполнение запроса всем пользователям
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email" => ['required', new ValidEmail()],
            "password" => ['required', 'string', new StrongPassword()],
        ];
    }

    public function messages(): array
    {
        return [
            "email.required" => "Email is required",
            "password.required" => "Password is required",
        ];
    }
}