<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchService
{
    /**
     * Deep search: nama/SKU produk, nama/SKU varian, nama game, dan developer.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function search(Builder $query, string $keyword): Builder
    {
        $like = '%'.addcslashes($keyword, '\\%_').'%';

        return $query->where(function (Builder $sub) use ($like): void {
            $sub->where('products.name', 'like', $like)
                ->orWhere('products.sku', 'like', $like)
                ->orWhereHas('variants', fn (Builder $variants) => $variants
                    ->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like))
                ->orWhereHas('games', fn (Builder $games) => $games
                    ->where('name', 'like', $like)
                    ->orWhereHas('developers', fn (Builder $developers) => $developers->where('name', 'like', $like)));
        });
    }

    /**
     * Varian yang menjadi alasan produk muncul, bila produk induk sendiri tidak cocok.
     */
    public function matchedVariant(Product $product, string $keyword): ?ProductVariant
    {
        $needle = mb_strtolower($keyword);
        if ($needle === '' || str_contains(mb_strtolower($product->name), $needle)
            || str_contains(mb_strtolower((string) $product->sku), $needle)) {
            return null;
        }

        return $product->variants->first(
            fn (ProductVariant $variant): bool => str_contains(mb_strtolower($variant->name), $needle)
                || str_contains(mb_strtolower((string) $variant->sku), $needle),
        );
    }
}
