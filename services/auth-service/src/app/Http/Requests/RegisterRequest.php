<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StrongPassword;
use App\Rules\ValidEmail;
use App\Rules\ValidName;

class RegisterRequest extends FormRequest
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
            "name" => ['required', 'string', new ValidName()],
            "email" => ['required', 'unique:users,email', new ValidEmail()],
            "password" => ['required', 'confirmed', new StrongPassword()],
            "password_confirmation" => "required|string",
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Name is required",
            "email.required" => "Email is required",
            "password.required" => "Password is required",
            "email.unique" => "Email already exists",
            "password.confirmed" => "Password confirmation does not match",
            "password_confirmation.required" => "Password confirmation is required",
        ];
    }
}