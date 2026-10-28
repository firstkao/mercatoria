<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\Store;
use Database\Seeders\StoreSeeder;

test('store seeder mengisi 3 toko default', function () {
    $this->seed(StoreSeeder::class);

    expect(Store::where('name', 'Official Store')->first())
        ->min_belanja_rmb->toEqual('100.00')
        ->ongkir_rmb->toEqual('7.00');

    expect(Store::where('name', 'Fanmade Studio')->first())
        ->min_belanja_rmb->toEqual('0.00')
        ->ongkir_rmb->toEqual('0.00');

    expect(Store::where('name', 'Toko Baju Collab')->first())
        ->min_belanja_rmb->toEqual('150.00')
        ->ongkir_rmb->toEqual('10.00');
});

test('store seeder dapat dijalankan ulang tanpa duplikat', function () {
    $this->seed(StoreSeeder::class);
    $count = Store::count();

    $this->seed(StoreSeeder::class);

    expect(Store::count())->toBe($count);
});

test('is free shipping saat ongkir nol', function () {
    expect(Store::factory()->create(['ongkir_rmb' => 0])->isFreeShipping())->toBeTrue()
        ->and(Store::factory()->create(['ongkir_rmb' => 7])->isFreeShipping())->toBeFalse();
});

test('has min belanja saat min lebih dari nol', function () {
    expect(Store::factory()->create(['min_belanja_rmb' => 100])->hasMinBelanja())->toBeTrue()
        ->and(Store::factory()->create(['min_belanja_rmb' => 0])->hasMinBelanja())->toBeFalse();
});

test('hapus store membuat referensi produk dan game jadi null', function () {
    $store = Store::factory()->create();
    $game = Game::factory()->create(['default_store_id' => $store->id]);
    $product = Product::factory()->create(['game_id' => $game->id, 'store_id' => $store->id]);

    $store->delete();

    expect($game->fresh()->default_store_id)->toBeNull()
        ->and($product->fresh()->store_id)->toBeNull();
});
