<?php

namespace Database\Factories;

use App\Models\Spammer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spammer>
 */
class SpammerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => '628'.fake()->numerify('#########'),
            'reason' => Spammer::REASON_NO_PURCHASE_3X,
            'status' => Spammer::STATUS_BLOCKED,
            'unblock_count' => 0,
            'banned_at' => now(),
        ];
    }
}
