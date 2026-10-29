<?php

use App\Models\Cart;
use App\Models\User;

test('navbar menampilkan total harga keranjang', function () {
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 2]);
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    $html = $this->actingAs($user)->get('/cart')->assertOk()->getContent();

    // 255rb×2 + 85rb = 595rb
    expect($html)->toContain(format_rp(595000));
});

test('navbar menampilkan badge jumlah item keranjang', function () {
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 42]);

    $html = $this->actingAs($user)->get('/cart')->assertOk()->getContent();

    expect($html)->toContain('text-white">42</span>');
});

test('badge keranjang tersembunyi saat kosong', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/cart')->assertOk()->getContent();

    expect($html)->toContain('aria-label="Keranjang"')
        ->and($html)->not->toContain('min-w-4 items-center justify-center rounded-full bg-black');
});

test('total keranjang hanya milik user pemilik', function () {
    $user = User::factory()->create();
    $lain = User::factory()->create();
    $produk = productFinal255();
    Cart::factory()->create(['user_id' => $lain->id, 'product_id' => $produk->id, 'qty' => 1]);

    // Dorong produk itu keluar dari footer "Produk Terpopuler" (top 5 sold_count)
    \App\Models\Product::factory()->count(5)->create(['sold_count' => 10, 'is_active' => true]);

    $html = $this->actingAs($user)->get('/cart')->assertOk()->getContent();

    expect($html)->not->toContain(format_rp(255000));
});
