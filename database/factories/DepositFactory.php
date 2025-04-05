<?php

namespace Database\Factories;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user' => $user->id,
            'value' => fake()->randomFloat(2, 1, 1000),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
        ];
    }
}
