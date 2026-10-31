<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchService
{
    /**
     * Deep search (PRD 13.7 / 13.13): nama & SKU produk, nama & SKU varian,
     * nama game, dan nama developer. Produk nonaktif disembunyikan.
     */
    public function search(string $keyword, array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $q = trim($keyword);

        $query = Product::query()
            ->where('is_active', true)
            ->with([
                'game',
                'tags',
                'merchCategory',
                'variants' => fn ($vq) => $vq->orderBy('id', 'desc'),
            ]);

        if ($q !== '') {
            $like = '%'.$q.'%';

            $query->where(function (Builder $sub) use ($like) {
                $sub->where('products.name', 'LIKE', $like)
                    ->orWhere('products.sku', 'LIKE', $like)
                    ->orWhereHas('variants', function (Builder $vq) use ($like) {
                        $vq->where('product_variants.name', 'LIKE', $like)
                            ->orWhere('product_variants.sku', 'LIKE', $like);
                    })
                    ->orWhereHas('game', fn (Builder $gq) => $gq->where('games.name', 'LIKE', $like))
                    ->orWhereHas('game.developers', fn (Builder $dq) => $dq->where('developers.name', 'LIKE', $like));
            });

            // Kecocokan judul produk diurutkan di atas kecocokan nama varian.
            $query->orderByRaw(
                'CASE WHEN products.name LIKE ? THEN 0 ELSE 1 END ASC',
                [$like]
            );

            // [FIX #1] Tambahan secondary sort — hasil yang sama-sama match diurutkan stabil.
            $query->orderByDesc('products.id');
        } else {
            // [FIX #1] Default sort saat tanpa keyword.
            $query->orderByDesc('products.id');
        }

        if (! empty($filters['game_id'])) {
            $query->where('products.game_id', $filters['game_id']);
        }

        if (! empty($filters['merch_category_id'])) {
            $query->where('products.merch_category_id', $filters['merch_category_id']);
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn ($tq) => $tq->where('tags.slug', $filters['tag']));
        }

        return $query->paginate($perPage)->withQueryString();
    }
}