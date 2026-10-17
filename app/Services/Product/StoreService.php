<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StoreService
{
    public function getAllForDropdown(): Collection
    {
        return Store::orderBy('name')
            ->get(['id', 'name', 'min_belanja_rmb', 'ongkir_rmb']);
    }

    public function create(array $data): Store
    {
        return Store::create([
            'name' => $data['name'],
            'min_belanja_rmb' => $data['min_belanja_rmb'] ?? 0,
            'ongkir_rmb' => $data['ongkir_rmb'] ?? 0,
        ]);
    }

    public function update(Store $store, array $data): Store
    {
        $store->update([
            'name' => $data['name'] ?? $store->name,
            'min_belanja_rmb' => $data['min_belanja_rmb'] ?? $store->min_belanja_rmb,
            'ongkir_rmb' => $data['ongkir_rmb'] ?? $store->ongkir_rmb,
        ]);

        return $store->fresh();
    }

    public function delete(Store $store): void
    {
        // FK nullOnDelete otomatis set NULL di products & games
        $store->delete();
    }

    public function getUsageCount(Store $store): array
    {
        return [
            'products' => $this->productsUsing($store)->count(),
            'games' => $store->games()->count(),
        ];
    }

    public function productsUsing(Store $store): Builder
    {
        return Product::query()
            ->where(function (Builder $query) use ($store): void {
                $query->where('store_id', $store->id)
                    ->orWhere(function (Builder $query) use ($store): void {
                        $query->whereNull('store_id')
                            ->whereHas('games', fn (Builder $gameQuery) => $gameQuery->where('default_store_id', $store->id));
                    });
            });
    }
}
