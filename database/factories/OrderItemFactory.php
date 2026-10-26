<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
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
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'price_at_checkout' => $this->faker->numberBetween(50000, 500000),
            'cost_price_at_checkout' => $this->faker->numberBetween(20000, 200000),
            'qty' => $this->faker->numberBetween(1, 5),
        ];
    }
}
