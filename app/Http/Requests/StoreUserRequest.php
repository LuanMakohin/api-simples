<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'document' => 'required|string|unique:users,document|regex:/^\d{11}|\d{14}$/',
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
            'document.regex' => 'The document must be a valid CPF (11 digits) or CNPJ (14 digits) without dots, dashes, or spaces.',

            'user_type.required' => 'The user type is required.',
            'user_type.in' => 'The user type must be either PF (individual) or PJ (business).',

            'balance.required' => 'The balance is required.',
            'balance.numeric' => 'The balance must be a valid number.',
            'balance.min' => 'The balance must be at least 0.',
        ];
    }
}
