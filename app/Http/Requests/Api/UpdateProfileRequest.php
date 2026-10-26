<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:30',
                'regex:/^(\+62|\+60|\+65|\+66|\+63)\d{6,14}$/',
                Rule::unique('users', 'phone_number')->ignore($this->user()?->id),
            ],
            'birthday' => ['nullable', 'array'],
            'birthday.day' => ['required_with:birthday', 'integer', 'between:1,31'],
            'birthday.month' => [
                'required_with:birthday',
                'string',
                Rule::in([
                    'January',
                    'February',
                    'March',
                    'April',
                    'May',
                    'June',
                    'July',
                    'August',
                    'September',
                    'October',
                    'November',
                    'December',
                ]),
            ],
            'birthday.year' => ['required_with:birthday', 'integer', 'between:1900,' . now()->year],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.unique' => 'The phone number has already been taken.',
            'phone_number.regex' => 'Phone number must be from Indonesia, Malaysia, Singapore, Thailand, or Philippines.',
            'birthday.day.between' => 'The birthday day must be between 1 and 31.',
            'birthday.month.in' => 'The birthday month is invalid.',
        ];
    }
}
