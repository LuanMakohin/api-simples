<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|exists:users,id',
            'value' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user.required' => 'The user ID is required.',
            'user.exists' => 'The specified user does not exist.',
            'value.required' => 'The value is required.',
            'value.numeric' => 'The value must be a number.',
            'value.min' => 'The value must be at least 0.01.',
        ];
    }
}
