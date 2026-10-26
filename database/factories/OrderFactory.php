<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'paid', 'shipped', 'completed', 'canceled']);

        return [
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('??????-####')),
            'user_id' => User::factory(),
            'voucher_id' => null,
            'recipient_name' => $this->faker->name(),
            'phone_number' => $this->faker->phoneNumber(),
            'shipping_address' => $this->faker->address(),
            'country' => 'Indonesia',
            'order_type' => $this->faker->randomElement(['domestic', 'international']),
            'shipping_courier' => 'J&T Express',
            'tracking_number' => $this->faker->optional(0.5)->numerify('###########'),
            'currency' => 'IDR',
            'exchange_rate' => 1,
            'subtotal' => 0,
            'qty_discount_amount' => 0,
            'voucher_discount_amount' => 0,
            'shipping_cost' => $this->faker->numberBetween(10000, 50000),
            'total_cost_price' => 0,
            'total_profit' => 0,
            'grand_total' => 0,
            'status' => $status,
        ];
    }
}
