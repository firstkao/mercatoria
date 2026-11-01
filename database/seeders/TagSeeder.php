<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['PRESALE' => 'presale', 'LIMITED' => 'limited'] as $name => $slug) {
            Tag::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
