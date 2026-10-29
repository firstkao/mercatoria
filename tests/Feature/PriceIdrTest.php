<?php

use App\Models\Product;
use App\Models\User;

test('landing mengirim price_idr harga final per produk', function () {
    $product = productFinal255();
    Product::factory()->create(['is_active' => false]); // tidak ikut

    $products = collect($this->get('/')->assertOk()->viewData('products'));

    expect($products)->toHaveCount(1)
        ->and($products->first()->price_idr)->toBe(255000.0);
});

test('price_idr produk diskon memakai harga setelah diskon', function () {
    $product = productFinal85(); // final 85.000
    $product->update(['discount_amount' => 10000]);

    $products = collect($this->get('/')->assertOk()->viewData('products'));

    expect((float) $products->first()->price_idr)->toBe(75000.0);
});

test('katalog game mengirim price_idr harga final per produk', function () {
    $product = productFinal255();

    // Katalog butuh login (Guest Barrier PRD 7.2)
    $response = $this->actingAs(User::factory()->create())
        ->get('/'.$product->game->slug)->assertOk();
    $products = collect($response->viewData('products')->items());

    expect($products->first()->price_idr)->toBe(255000.0);
});

test('penawaran terbaik di landing punya price_idr', function () {
    $product = productFinal255();
    $product->update(['discount_amount' => 5000]);

    $bestOffers = collect($this->get('/')->assertOk()->viewData('bestOffers'));

    expect($bestOffers)->toHaveCount(1)
        ->and((float) $bestOffers->first()->price_idr)->toBe(250000.0);
});
