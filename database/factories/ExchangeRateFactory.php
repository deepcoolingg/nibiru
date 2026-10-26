<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = [
            'USD' => $this->faker->numberBetween(15000, 16500),
            'SGD' => $this->faker->numberBetween(11000, 12500),
            'MYR' => $this->faker->numberBetween(3000, 3500),
            'THB' => $this->faker->numberBetween(400, 450),
            'PHP' => $this->faker->numberBetween(250, 300),
        ];

        $currency = array_rand($currencies);

        return [
            'currency_code' => $currency,
            'rate_to_idr' => $currencies[$currency],
        ];
    }
}
