<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('user belum verifikasi email ditolak saat buat pesanan', function () {
    $user = User::factory()->unverified()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertRedirect('/email/verify');

    expect(Order::count())->toBe(0);
});

test('user tanpa tanggal lahir ditolak saat buat pesanan', function () {
    $user = User::factory()->create();
    $user->forceFill(['tgl_lahir' => null])->save();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertRedirect('/profile/complete');

    expect(Order::count())->toBe(0);
});

test('user terverifikasi dengan tgl lahir lolos gate checkout', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertRedirect();

    expect(Order::count())->toBe(1);
});

test('gate juga berlaku untuk konfirmasi pembayaran', function () {
    // Skema DB 16.2: canOrderOrConfirmPayment — Buat Pesanan & Konfirmasi Pembayaran
    $user = User::factory()->unverified()->create();
    [$owner, $order] = placeTestOrder($user);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect('/email/verify');

    expect(Payment::count())->toBe(0);
});

test('pesan gate ditampilkan jelas', function () {
    $user = User::factory()->unverified()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertSessionHas('status');
});
