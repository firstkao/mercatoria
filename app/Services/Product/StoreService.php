<?php

namespace App\Services\Product;

use App\Models\Store;
use Illuminate\Support\Collection;

class StoreService
{
    /**
     * Semua store untuk dropdown admin.
     */
    public function getAllForDropdown(): Collection
    {
        return Store::orderBy('name')
            ->get(['id', 'name', 'min_belanja_rmb', 'ongkir_rmb']);
    }

    /**
     * @param  array{name: string, min_belanja_rmb?: float|int|string, ongkir_rmb?: float|int|string}  $data
     */
    public function create(array $data): Store
    {
        return Store::create([
            'name' => $data['name'],
            'min_belanja_rmb' => $data['min_belanja_rmb'] ?? 0,
            'ongkir_rmb' => $data['ongkir_rmb'] ?? 0,
        ]);
    }

    /**
     * @param  array{name?: string, min_belanja_rmb?: float|int|string, ongkir_rmb?: float|int|string}  $data
     */
    public function update(Store $store, array $data): Store
    {
        $store->update([
            'name' => $data['name'] ?? $store->name,
            'min_belanja_rmb' => $data['min_belanja_rmb'] ?? $store->min_belanja_rmb,
            'ongkir_rmb' => $data['ongkir_rmb'] ?? $store->ongkir_rmb,
        ]);

        return $store->fresh();
    }

    /**
     * Hapus store. Referensi produk & game otomatis NULL (FK nullOnDelete).
     */
    public function delete(Store $store): void
    {
        $store->delete();
    }

    /**
     * @return array{products: int, games: int}
     */
    public function getUsageCount(Store $store): array
    {
        return [
            'products' => $store->products()->count(),
            'games' => $store->games()->count(),
        ];
    }
}
