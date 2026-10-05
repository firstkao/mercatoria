<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ShippingTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'game_id' => null,
            'developer_id' => null,
            'shipping_tier_id' => ShippingTier::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'tag' => null,
            'sale_starts_at' => null,
            'sale_ends_at' => null,
            'is_published' => true,
        ];
    }
}
