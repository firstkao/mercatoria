<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Test murah untuk tombol aksi: form POST + action route benar (bukan link GET semu).
 */
test('tombol logout di layout pakai form post dengan csrf', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->toContain('action="'.route('logout').'"')
        ->toContain('method="POST"')
        ->toContain('_token');
});

test('tombol hapus cart pakai form delete', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $html = $this->actingAs($user)->get('/cart')->getContent();

    expect($html)->toContain('action="'.route('cart.destroy', $item).'"')
        ->toContain('method="POST"')
        ->toContain('_token');
});

test('form checkout dan upload bukti memakai post benar', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $checkoutHtml = $this->actingAs($user)->get('/checkout?items[]='.$item->id)->getContent();
    expect($checkoutHtml)->toContain('action="'.route('checkout.store').'"')
        ->toContain('method="POST"');

    $orderHtml = $this->actingAs($user)->get('/orders/'.$order->no_order)->getContent();
    expect($orderHtml)->toContain('action="'.route('orders.payments.store', $order->no_order).'"')
        ->toContain('method="POST"')
        ->toContain('enctype="multipart/form-data"');
});

test('tombol admin hapus produk pakai form delete', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $html = $this->actingAs($admin)->get('/office/products/'.$product->id.'/edit')->getContent();

    expect($html)->toContain('action="'.route('office.products.destroy', $product).'"')
        ->toContain('method="POST"');
});

test('form edit profil dan ganti password pakai post', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/profile/edit')->getContent();

    expect($html)->toContain('action="'.route('profile.update').'"')
        ->toContain('action="'.route('profile.password').'"')
        ->toContain('method="POST"');
});
