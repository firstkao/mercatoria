<?php

namespace App\Services\Product;

use App\Models\MerchCategory;

class MerchCategoryService
{
    /**
     * Resolve slug kategori berdasarkan nama produk.
     * Fallback: slug yang dikirim → 'other'.
     */
    public function resolveSlug(string $name, ?string $slug = null): string
    {
        return MerchCategory::matchFor($name, $slug ?? '')?->slug
            ?? $slug
            ?? 'other';
    }
}
