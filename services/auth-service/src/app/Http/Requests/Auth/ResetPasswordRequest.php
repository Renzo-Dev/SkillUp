<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'token' => 'required|string',
      'password' => 'required|string|min:6|confirmed',
    ];
  }

  public function messages(): array
  {
    return [
      'token.required' => 'Reset token is required',
      'password.required' => 'Password is required',
      'password.min' => 'Password must be at least 6 characters',
      'password.confirmed' => 'Password confirmation does not match',
    ];
  }
}
