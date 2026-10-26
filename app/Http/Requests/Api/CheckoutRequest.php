<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
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
    public const ALLOWED_COUNTRIES = [
        'Indonesia',
        'Malaysia',
        'Singapore',
        'Thailand',
        'Philippines',
    ];

    public const COUNTRY_CURRENCY_MAP = [
        'Indonesia' => ['IDR'],
        'Malaysia' => ['MYR'],
        'Singapore' => ['SGD'],
        'Thailand' => ['THB'],
        'Philippines' => ['PHP'],
    ];

    public const ALLOWED_CURRENCIES = ['IDR', 'MYR', 'SGD', 'THB', 'PHP'];

    public function rules(): array
    {
        $countries = implode(',', self::ALLOWED_COUNTRIES);

        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'country' => ['required', 'string', "in:{$countries}"],
            'currency' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_CURRENCIES)],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'voucher_code' => ['nullable', 'string', 'exists:vouchers,code'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $country = (string) $this->input('country');
            $currency = (string) $this->input('currency');

            if (! isset(self::COUNTRY_CURRENCY_MAP[$country])) {
                return;
            }

            if (! in_array($currency, self::COUNTRY_CURRENCY_MAP[$country], true)) {
                $validator->errors()->add(
                    'currency',
                    "Currency {$currency} is not supported for country {$country}."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'country.in' => 'Pengiriman hanya tersedia ke: Indonesia, Malaysia, Singapore, Thailand, Philippines.',
        ];
    }
}
