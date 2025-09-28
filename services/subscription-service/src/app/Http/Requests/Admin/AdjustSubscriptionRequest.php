<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'status' => ['sometimes', 'in:trial,active,grace,cancelled,expired'],
            'plan_id' => ['sometimes', 'uuid'],
            'expires_at' => ['sometimes', 'date'],
            'auto_renew' => ['sometimes', 'boolean'],
        ];
    }
}
