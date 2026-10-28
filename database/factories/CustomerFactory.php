<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'email' => fn (array $attributes) => User::find($attributes['user_id'])?->email ?? fake()->unique()->safeEmail(),
            'no_hp' => fn (array $attributes) => User::find($attributes['user_id'])?->no_hp ?? '628'.fake()->numerify('#########'),
            'current_state' => 'MVP',
            'unblock_count' => 0,
            'unblock_grace_until' => null,
            'tier' => 'silver',
            'total_belanja' => 0,
            'state_updated_at' => now(),
        ];
    }
}
