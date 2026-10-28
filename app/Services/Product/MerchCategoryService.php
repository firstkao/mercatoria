<?php

namespace App\Services\Product;

use App\Models\MerchCategory;

class MerchCategoryService
{
    /**
     * Cari kategori merch dari nama/slug produk (PRD 14 — scan keyword
     * berdasarkan prioritas dari atas; tidak match = other).
     */
    public function resolveSlug(string $name): string
    {
        $haystack = mb_strtolower($name);

        $categories = MerchCategory::orderBy('priority')->get();

        foreach ($categories as $category) {
            foreach ($category->keywords ?? [] as $keyword) {
                if (str_contains($haystack, mb_strtolower($keyword))) {
                    return $category->slug;
                }
            }
        }

        // Fallback: kategori berprioritas terakhir ("Other" — PRD 14 prioritas 8).
        return MerchCategory::orderByDesc('priority')->value('slug') ?? 'other';
    }

    /**
     * Set merch_category_id produk dari nama produk, kecuali sudah di-override admin.
     */
    public function applyToProductName(string $productName): ?int
    {
        $slug = $this->resolveSlug($productName);

        return MerchCategory::where('slug', $slug)->value('id');
    }
}
