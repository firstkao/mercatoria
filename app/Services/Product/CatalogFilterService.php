<?php

namespace App\Services\Product;

use App\Services\Order\ShippingCalculatorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * PRD 13.7: filter (tag, ketersediaan, rentang harga) & sort katalog/pencarian.
 */
class CatalogFilterService
{
    public const SORTS = ['termurah', 'termahal', 'terpopuler', 'terbaru', 'abjad'];

    public function __construct(private ShippingCalculatorService $shipping) {}

    public function apply(Builder $query, Request $request): Builder
    {
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn (Builder $tq) => $tq->where('tags.slug', (string) $request->query('tag')));
        }

        if (in_array($request->query('availability'), ['tersedia', 'kosong'], true)) {
            $query->where('products.availability', $request->query('availability'));
        }

        $min = $this->amount($request->query('min_price'));
        $max = $this->amount($request->query('max_price'));
        if ($min !== null || $max !== null) {
            // Harga final dihitung di PHP (bobot, ongkir & diskon aktif), bukan kolom SQL.
            $ids = (clone $query)->with(['game.defaultStore', 'store', 'variants'])->get()
                ->filter(function ($product) use ($min, $max) {
                    $price = $this->shipping->calculateItemShipping($product)['final_price_bersih'];

                    return ($min === null || $price >= $min) && ($max === null || $price <= $max);
                })
                ->pluck('id');
            $query->whereIn('products.id', $ids);
        }

        return $this->sort($query, $request->query('sort'));
    }

    public function sort(Builder $query, mixed $sort): Builder
    {
        return match ($sort) {
            'termurah' => $query->reorder('products.price_rmb')->orderBy('products.id'),
            'termahal' => $query->reorder('products.price_rmb', 'desc')->orderBy('products.id'),
            'terpopuler' => $query->reorder('products.sold_count', 'desc')->orderByDesc('products.id'),
            'terbaru' => $query->reorder('products.id', 'desc'),
            'abjad' => $query->reorder('products.name')->orderBy('products.id'),
            default => $query,
        };
    }

    private function amount(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value >= 0 ? (float) $value : null;
    }
}
