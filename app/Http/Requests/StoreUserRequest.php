<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
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
     * @return array<string, string|array>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'document' => [
                'required',
                'string',
                'unique:users,document',
                'regex:/^\d+$/',
            ],
            'user_type' => 'required|in:PF,PJ',
            'balance' => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name must not exceed 255 characters.',

            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email is already taken.',
            'email.max' => 'The email must not exceed 255 characters.',

            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',

            'document.required' => 'The document is required.',
            'document.string' => 'The document must be a valid string.',
            'document.unique' => 'The document is already registered.',
            'document.regex' => 'The document must contain only digits.',

            'user_type.required' => 'The user type is required.',
            'user_type.in' => 'The user type must be either PF (individual) or PJ (business).',

            'balance.required' => 'The balance is required.',
            'balance.numeric' => 'The balance must be a valid number.',
            'balance.min' => 'The balance must be at least 0.',
        ];
    }

    /**
     * Apply additional conditional validation after base validation.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $this->all();

            if (isset($data['user_type'], $data['document'])) {
                $length = strlen($data['document']);

                if ($data['user_type'] === 'PF' && $length !== 11) {
                    $validator->errors()->add('document', 'The document must be 11 digits for individuals (PF).');
                }

                if ($data['user_type'] === 'PJ' && $length !== 14) {
                    $validator->errors()->add('document', 'The document must be 14 digits for businesses (PJ).');
                }
            }
        });
    }
}
