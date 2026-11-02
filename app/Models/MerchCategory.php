<?php

namespace App\Models;

use App\Models\Concerns\CachesNavList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MerchCategory extends Model
{
    use CachesNavList;
    use HasFactory;

    public const NAV_CACHE_KEY = 'nav_merch_categories';

    protected $fillable = [
        'name',
        'slug',
        'priority',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'keywords' => 'array',
        ];
    }

    /**
     * PRD 14: auto-group — scan nama + slug produk terhadap keyword
     * kategori, prioritas (priority terkecil) menang.
     */
    public static function matchFor(string $name, string $slug = ''): ?self
    {
        $haystack = Str::lower($name.' '.$slug);

        foreach (static::navList() as $category) {
            foreach ((array) $category->keywords as $keyword) {
                $keyword = Str::lower(trim((string) $keyword));

                if ($keyword !== '' && str_contains($haystack, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    protected static function navQuery(): Builder
    {
        return static::orderBy('priority')->orderBy('id');
    }
}
