<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('guest tidak bisa membuka keranjang', function () {
    $this->get('/cart')->assertRedirect('/login');
});

test('user dapat menambah produk ke keranjang', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->post('/cart', [
        'product_id' => $product->id,
        'qty' => 2,
    ])->assertRedirect('/cart');

    expect(Cart::where('user_id', $user->id)->first())
        ->product_id->toBe($product->id)
        ->qty->toBe(2);
});

test('item sama diakumulasi bukan duplikat', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'qty' => 1]);
    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'qty' => 3]);

    expect(Cart::where('user_id', $user->id)->count())->toBe(1)
        ->and(Cart::where('user_id', $user->id)->first()->qty)->toBe(4);
});

test('varian berbeda jadi baris terpisah', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variantA = ProductVariant::factory()->create(['product_id' => $product->id]);
    $variantB = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'variant_id' => $variantA->id, 'qty' => 1]);
    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'variant_id' => $variantB->id, 'qty' => 1]);

    expect(Cart::where('user_id', $user->id)->count())->toBe(2);
});

test('qty dapat diubah dan item dapat dihapus', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'qty' => 1]);

    $this->actingAs($user)->patch("/cart/{$item->id}", ['qty' => 5]);
    expect($item->fresh()->qty)->toBe(5);

    $this->actingAs($user)->delete("/cart/{$item->id}");
    expect(Cart::where('id', $item->id)->exists())->toBeFalse();
});

test('user tidak bisa mengubah keranjang user lain', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $other->id, 'qty' => 1]);

    $this->actingAs($user)->patch("/cart/{$item->id}", ['qty' => 9])->assertForbidden();
    expect($item->fresh()->qty)->toBe(1);
});
