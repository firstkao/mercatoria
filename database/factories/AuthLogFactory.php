<?php

namespace Database\Factories;

use App\Models\AuthLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthLog>
 */
class AuthLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'email_attempt' => fake()->unique()->safeEmail(),
            'action' => AuthLog::ACTION_LOGIN_FAILED,
            'reason' => 'password salah',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ];
    }
}
