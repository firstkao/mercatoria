<?php

namespace App\Services\Product;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductSearchService
{
    public function search(string $keyword, array $filters = [], int $perPage = 30, ?Request $request = null): LengthAwarePaginator
    {
        $q = trim($keyword);

        $query = Product::query()
            ->where('is_active', true)
            ->with([
                'game.defaultStore',
                'store',
                'developer',
                'tags',
                'merchCategory',
                'variants' => fn ($vq) => $vq->orderBy('id', 'desc'),
            ]);

        // Cek apakah keyword match nama game
        $matchedGame = null;
        if ($q !== '') {
            $matchedGame = Game::where('is_active', true)
                ->where('name', 'LIKE', $q)
                ->first();
        }

        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';

            $query->where(function (Builder $sub) use ($like, $matchedGame) {
                // Kalau match nama game → batasi hanya produk dari game itu
                if ($matchedGame) {
                    $sub->where('products.game_id', $matchedGame->id);
                }

                // Tetap deep search di dalam scope yang sudah dibatasi
                $sub->where(function (Builder $inner) use ($like) {
                    $inner->where('products.name', 'LIKE', $like)
                        ->orWhere('products.sku', 'LIKE', $like)
                        ->orWhereHas('variants', function (Builder $vq) use ($like) {
                            $vq->where('product_variants.name', 'LIKE', $like)
                                ->orWhere('product_variants.sku', 'LIKE', $like);
                        })
                        ->orWhereHas('developer', fn (Builder $dq) => $dq->where('developers.name', 'LIKE', $like))
                        ->orWhereHas('game.developers', fn (Builder $dq) => $dq->where('developers.name', 'LIKE', $like));
                });
            });

            $query->orderByRaw(
                'CASE WHEN products.name LIKE ? THEN 0 ELSE 1 END ASC',
                [$like]
            );
            $query->orderByDesc('products.id');
        } else {
            $query->orderByDesc('products.id');
        }

        // Filter dari request
        if (! empty($filters['game_id'])) {
            $query->where('products.game_id', $filters['game_id']);
        }

        if (! empty($filters['developer_id'])) {
            $query->where(function (Builder $dq) use ($filters) {
                $dq->where('products.developer_id', $filters['developer_id'])
                    ->orWhereHas('game.developers', fn (Builder $gq) => $gq->where('developers.id', $filters['developer_id']));
            });
        }

        if (! empty($filters['merch_category_id'])) {
            $query->where('products.merch_category_id', $filters['merch_category_id']);
        }

        if ($request) {
            app(CatalogFilterService::class)->apply($query, $request);
        } elseif (! empty($filters['tag'])) {
            $query->whereHas('tags', fn ($tq) => $tq->where('tags.slug', $filters['tag']));
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
