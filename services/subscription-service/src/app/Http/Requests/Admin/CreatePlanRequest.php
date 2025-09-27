<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
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
            'code' => ['required', 'string'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'in:monthly,yearly,lifetime'],
            'trial_period_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*.feature_key' => ['required_with:features', 'string'],
            'features.*.limit_value' => ['nullable', 'integer', 'min:0'],
            'features.*.metadata' => ['nullable', 'array'],
        ];
    }
}
