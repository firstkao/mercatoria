<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchCategory extends Model
{
    protected $fillable = ['name', 'slug', 'priority', 'keywords'];

    protected function casts(): array
    {
        return [
            // [FIX #2] WAJIB — kalau kolom JSON, tanpa ini foreach iterates per karakter.
            'keywords' => 'array',
            'priority' => 'integer',
        ];
    }

    /**
     * [Dipindah dari MerchCategoryService] Cari kategori dari nama/slug produk (PRD 14).
     * Scan keyword berdasarkan prioritas; tidak match = fallback "Other".
     */
    public static function matchFor(string $name, ?string $slug = null): ?self
    {
        $haystack = mb_strtolower($name.' '.($slug ?? ''));

        $categories = static::orderBy('priority')->get();

        foreach ($categories as $category) {
            foreach ($category->keywords ?? [] as $keyword) {
                if (str_contains($haystack, mb_strtolower($keyword))) {
                    return $category;
                }
            }
        }

        // Fallback: kategori prioritas terakhir ("Other").
        return static::orderByDesc('priority')->first();
    }
}