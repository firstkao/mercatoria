<?php

namespace Database\Factories;

use App\Models\MerchCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MerchCategory>
 */
class MerchCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'priority' => fake()->numberBetween(1, 8),
            'keywords' => [],
        ];
    }
}
