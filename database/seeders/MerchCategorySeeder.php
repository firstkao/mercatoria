<?php

namespace Database\Seeders;

use App\Models\MerchCategory;
use Illuminate\Database\Seeder;

class MerchCategorySeeder extends Seeder
{
    /**
     * 8 kategori auto-group sesuai PRD 14 — urutan = prioritas cek.
     *
     * @var array<int, array{name: string, slug: string, priority: int, keywords: array<int, string>}>
     */
    private const CATEGORIES = [
        ['name' => 'Shikishi', 'slug' => 'shikishi', 'priority' => 1, 'keywords' => ['shikishi', 'shikishi board', 'folding shikishi']],
        ['name' => 'Stand', 'slug' => 'stand', 'priority' => 2, 'keywords' => ['standee', 'acrylic stand', 'stand']],
        ['name' => 'Pendant', 'slug' => 'pendant', 'priority' => 3, 'keywords' => ['pendant', 'acrylic pendant']],
        ['name' => 'Plushie', 'slug' => 'plush', 'priority' => 4, 'keywords' => ['plush', 'plushie', 'chibigurumi', 'doll']],
        ['name' => 'Keychain', 'slug' => 'keychain', 'priority' => 5, 'keywords' => ['keychain', 'acrylic charm', 'acrylic keychain']],
        ['name' => 'Badge', 'slug' => 'badge', 'priority' => 6, 'keywords' => ['badge', 'pin', 'big badge', 'oval badge', 'square badge']],
        ['name' => 'Apparel', 'slug' => 'apparel', 'priority' => 7, 'keywords' => ['t-shirt', 'shirt', 'hoodie', 'jacket', 'shoes', 'sweater']],
        ['name' => 'Other', 'slug' => 'other', 'priority' => 8, 'keywords' => []],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            MerchCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'priority' => $category['priority'],
                    'keywords' => $category['keywords'],
                ]
            );
        }
    }
}
