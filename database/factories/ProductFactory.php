<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $palette = [
            ['name' => 'Lemonberry', 'hex' => '#E9D76D'],
            ['name' => 'Blackberry', 'hex' => '#1F2A44'],
            ['name' => 'Strawberry', 'hex' => '#F3B4CC'],
            ['name' => 'Blueberry', 'hex' => '#77BDF7'],
            ['name' => 'Cloudberry', 'hex' => '#E8E1D7'],
        ];

        $color = $this->faker->randomElement($palette);
        $price = $this->faker->numberBetween(50000, 500000);
        $costPrice = (int) ($price * 0.4);

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'image' => 'products/placeholder-' . $this->faker->numberBetween(1, 5) . '.jpg',
            'size_fit_image' => 'products/size-fit-' . $this->faker->numberBetween(1, 5) . '.jpg',
            'color_name' => $color['name'],
            'color_hex' => $color['hex'],
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-??')),
            'price' => $price,
            'cost_price' => $costPrice,
            'stock' => $this->faker->numberBetween(10, 100),
            'weight' => $this->faker->numberBetween(100, 5000),
            'is_active' => true,
        ];
    }
}

