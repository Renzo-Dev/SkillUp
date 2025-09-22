<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BanUserRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'reason' => 'required|string|max:500',
    ];
  }

  public function messages(): array
  {
    return [
      'reason.required' => 'Ban reason is required',
      'reason.max' => 'Ban reason must not exceed 500 characters',
    ];
  }
}
