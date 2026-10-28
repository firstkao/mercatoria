<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;

test('effective store memakai store produk bila diisi', function () {
    $storeProduk = Store::factory()->create(['name' => 'Store Produk']);
    $storeGame = Store::factory()->create(['name' => 'Store Game']);
    $game = Game::factory()->create(['default_store_id' => $storeGame->id]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'store_id' => $storeProduk->id,
    ]);

    expect($product->effectiveStore()?->id)->toBe($storeProduk->id);
});

test('effective store fallback ke default store game', function () {
    $storeGame = Store::factory()->create();
    $game = Game::factory()->create(['default_store_id' => $storeGame->id]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'store_id' => null,
    ]);

    expect($product->effectiveStore()?->id)->toBe($storeGame->id);
});

test('effective store null bila tidak ada store sama sekali', function () {
    $game = Game::factory()->create(['default_store_id' => null]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'store_id' => null,
    ]);

    expect($product->effectiveStore())->toBeNull();
});

test('effective weight varian menang atas produk', function () {
    $product = Product::factory()->create(['weight_gram' => 500]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'weight_gram' => 80,
    ]);
    $variantTanpaBerat = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'weight_gram' => null,
    ]);

    expect($variant->effectiveWeight())->toBe(80)
        ->and($variantTanpaBerat->effectiveWeight())->toBe(500)
        ->and($product->effectiveWeight())->toBe(500);
});

test('effective price rmb varian menang atas produk', function () {
    $product = Product::factory()->create(['price_rmb' => 100]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_rmb' => 58,
    ]);
    $variantTanpaHarga = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_rmb' => null,
    ]);

    expect($variant->effectivePriceRmb())->toEqual(58.0)
        ->and($variantTanpaHarga->effectivePriceRmb())->toEqual(100.0);
});

test('has active discount hanya dalam periode', function () {
    $aktif = Product::factory()->create([
        'discount_amount' => 10000,
        'discount_start' => now()->subDay(),
        'discount_end' => now()->addDay(),
    ]);
    $belumMulai = Product::factory()->create([
        'discount_amount' => 10000,
        'discount_start' => now()->addDay(),
        'discount_end' => now()->addDays(5),
    ]);
    $sudahLewat = Product::factory()->create([
        'discount_amount' => 10000,
        'discount_start' => now()->subDays(5),
        'discount_end' => now()->subDay(),
    ]);
    $tanpaDiskon = Product::factory()->create(['discount_amount' => null]);

    expect($aktif->hasActiveDiscount())->toBeTrue()
        ->and($belumMulai->hasActiveDiscount())->toBeFalse()
        ->and($sudahLewat->hasActiveDiscount())->toBeFalse()
        ->and($tanpaDiskon->hasActiveDiscount())->toBeFalse();
});
