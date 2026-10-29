<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('tpl_??????'),
            'subject' => 'Subjek '.fake()->words(3, true),
            'body' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
