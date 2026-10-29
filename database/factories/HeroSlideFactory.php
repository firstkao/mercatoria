<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => '/images/hero-'.fake()->unique()->numberBetween(1, 99999).'.jpg',
            'url' => fake()->boolean(70) ? '/promo/'.fake()->slug(2) : null,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
