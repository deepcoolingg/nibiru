<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone_number' => preg_replace('/\s+/', '', (string) $this->input('phone_number')),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => [
                'required',
                'string',
                'max:30',
                'regex:/^(\+62|\+60|\+65|\+66|\+63)\d{6,14}$/',
                'unique:users,phone_number',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'birthday' => ['required', 'array'],
            'birthday.day' => ['required', 'integer', 'between:1,31'],
            'birthday.month' => ['required', 'string', 'in:January,February,March,April,May,June,July,August,September,October,November,December'],
            'birthday.year' => ['required', 'integer', 'between:1900,'.date('Y')],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be from Indonesia, Malaysia, Singapore, Thailand, or Philippines.',
        ];
    }
}
