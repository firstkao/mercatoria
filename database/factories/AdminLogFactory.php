<?php

namespace Database\Factories;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminLog>
 */
class AdminLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'action' => 'update',
            'model' => 'Product',
            'model_id' => 1,
            'old_value' => null,
            'new_value' => null,
            'description' => fake()->sentence(),
            'ip_address' => '127.0.0.1',
        ];
    }
}
