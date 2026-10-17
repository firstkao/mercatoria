<?php

namespace Database\Seeders;

use App\Models\MerchCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MerchCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Shikishi', ['shikishi', 'shikisi', 'art board', 'mini shikishi']],
            ['Standee', ['standee', 'acrylic stand', 'akrilik', 'stand']],
            ['Pendant', ['pendant', 'acrylic pendant']],
            ['Plush', ['plush', 'plushie', 'boneka', 'nui', 'doll', 'nuigurumi']],
            ['Keychain', ['keychain', 'key chain', 'gantungan', 'keyring', 'acrylic charm']],
            ['Badge', ['badge', 'pin', 'can badge', 'oval badge', 'square badge', 'round badge']],
            ['Apparel', ['apparel', 't-shirt', 'kaos', 'hoodie', 'jaket', 'impression wear']],
            ['Other', []],
        ];

        foreach ($categories as $i => [$name, $keywords]) {
            MerchCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'keyword_list' => $keywords,
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
