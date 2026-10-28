<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('VCH-####??')),
            'name' => fake()->sentence(3),
            'type' => 'nominal',
            'nominal' => 25000,
            'min_belanja' => 100000,
            'is_active' => true,
        ];
    }
}
