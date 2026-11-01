<?php

namespace App\Services\Product;

use App\Models\MerchCategory;

class MerchCategoryService
{
    public function resolveSlug(string $name, ?string $slug = null): string
    {
        return MerchCategory::matchFor($name, $slug ?? '')?->slug ?? 'other';
    }
}
