<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->word(),
            'image_path' => null,
            'price_yuan' => fake()->randomFloat(2, 10, 500),
            'compare_price_yuan' => null,
            'weight_grams' => fake()->numberBetween(50, 2000),
            'status' => ProductVariant::STATUS_AVAILABLE,
            'sort_order' => 0,
        ];
    }
}
