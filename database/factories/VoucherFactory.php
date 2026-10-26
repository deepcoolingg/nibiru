<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('VOUCHER-########')),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_qty' => 1,
            'max_uses' => $this->faker->numberBetween(50, 200),
            'used_count' => 0,
            'valid_until' => $this->faker->dateTimeBetween('now', '+6 months'),
            'is_active' => true,
        ];
    }
}
