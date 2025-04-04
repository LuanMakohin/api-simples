<?php

namespace Database\Factories;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payer = User::factory()->create();
        $payee = User::factory()->create();

        return [
            'user_payer_id' => $payer->id,
            'user_payee_id' => $payee->id,
            'value' => fake()->randomFloat(2, 1, 1000),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
        ];
    }
}
