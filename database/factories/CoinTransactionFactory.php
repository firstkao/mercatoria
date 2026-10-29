<?php

namespace Database\Factories;

use App\Models\CoinTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoinTransaction>
 */
class CoinTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'order_id' => null,
            'type' => CoinTransaction::TYPE_EARNED_SHARE,
            'amount' => 5,
            'balance_after' => 5,
            'expired_at' => null,
            'note' => fake()->sentence(),
        ];
    }
}
