<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'nullable|string|max:255',
      'email' => 'nullable|email|unique:users,email,' . $this->user()->id,
    ];
  }

  public function messages(): array
  {
    return [
      'name.max' => 'Name must not exceed 255 characters',
      'email.email' => 'Email must be valid',
      'email.unique' => 'Email already exists',
    ];
  }
}
