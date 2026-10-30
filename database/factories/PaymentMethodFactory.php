<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('pay???'),
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['bank_transfer', 'qr_code']),
            'fee' => fake()->numberBetween(0, 5000),
            'instructions' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
