<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'current_password' => 'required|string',
      'new_password' => 'required|string|min:6|confirmed',
    ];
  }

  public function messages(): array
  {
    return [
      'current_password.required' => 'Current password is required',
      'new_password.required' => 'New password is required',
      'new_password.min' => 'New password must be at least 6 characters',
      'new_password.confirmed' => 'New password confirmation does not match',
    ];
  }
}
