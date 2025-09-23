<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'role' => 'required|in:user,admin',
    ];
  }

  public function messages(): array
  {
    return [
      'role.required' => 'Role is required',
      'role.in' => 'Role must be user or admin',
    ];
  }
}
