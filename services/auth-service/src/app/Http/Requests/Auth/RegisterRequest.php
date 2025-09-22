<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email|max:255',
      'password' => 'required|string|min:6|confirmed',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Name is required',
      'name.max' => 'Name must not exceed 255 characters',
      'email.required' => 'Email is required',
      'email.email' => 'Email must be valid',
      'email.unique' => 'Email already exists',
      'password.required' => 'Password is required',
      'password.min' => 'Password must be at least 6 characters',
      'password.confirmed' => 'Password confirmation does not match',
    ];
  }
}
