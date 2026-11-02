<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'game_id' => Game::factory(),
            'store_id' => null,
            'merch_category_id' => null,
            'name' => ucwords($name),
            'sku' => strtoupper(Str::random(10)),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'price_rmb' => fake()->numberBetween(10, 300),
            'weight_gram' => null,
            'type' => Product::TYPE_SINGLE,
            'image_path' => null,
            'description_image_path' => null,
            'discount_amount' => null,
            'discount_start' => null,
            'discount_end' => null,
            'availability' => Product::AVAIL_TERSEDIA,
            'is_active' => true,
            'sold_count' => 0,
        ];
    }
}
