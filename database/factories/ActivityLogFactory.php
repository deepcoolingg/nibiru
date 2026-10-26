<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['create', 'update', 'delete', 'view', 'export', 'import'];

        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement($actions),
            'description' => $this->faker->sentence(),
            'metadata' => null,
        ];
    }
}
