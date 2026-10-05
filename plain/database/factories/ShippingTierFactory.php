<?php

namespace Database\Factories;

use App\Models\ShippingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingTier>
 */
class ShippingTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'T' . fake()->unique()->numberBetween(100, 999),
            'fee_yuan' => fake()->randomFloat(2, 5, 50),
            'min_purchase_yuan' => fake()->randomFloat(2, 100, 500),
        ];
    }
}
