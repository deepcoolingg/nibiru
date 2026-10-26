<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => 'xendit_invoice',
            'gateway' => 'xendit',
            'gateway_transaction_id' => $this->faker->unique()->bothify('TRX-############'),
            'amount' => $this->faker->numberBetween(50000, 500000),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'paid_at' => $this->faker->optional()->dateTime(),
            'raw_response' => null,
        ];
    }
}
