<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'product_id' => Product::factory(),
            'name' => ucwords($name),
            'sku' => strtoupper(Str::random(10)),
            'price_rmb' => null,
            'weight_gram' => null,
            'availability' => 'tersedia',
            'image_path' => null,
        ];
    }
}
