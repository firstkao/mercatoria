<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ShareClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShareClick>
 */
class ShareClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'product_id' => Product::factory(),
            'ip_address' => fake()->ipv4(),
            'country_code' => 'ID',
        ];
    }
}
