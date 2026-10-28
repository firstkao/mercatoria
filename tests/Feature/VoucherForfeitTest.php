<?php

use App\Models\Cart;
use App\Models\User;
use App\Services\User\DiscountForfeitureService;

test('voucher hangus permanen saat pembayaran gagal', function () {
    [$user, $order, $userVoucher] = placeOrderUsingVoucher();

    expect($userVoucher->fresh()->status)->toBe('used_at_checkout');

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($userVoucher->fresh()->status)->toBe('hangus');
});

test('voucher hangus tidak bisa dipakai lagi', function () {
    [$user, $order, $userVoucher] = placeOrderUsingVoucher();

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($userVoucher->fresh()->isUsable())->toBeFalse();

    $itemB = Cart::factory()->create([
        'user_id' => $user->id,
        'product_id' => productFinal255()->id,
        'qty' => 1,
    ]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemB->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');

    expect($userVoucher->fresh()->status)->toBe('hangus');
});

test('voucher hangus tidak kembali active setelah semua proses', function () {
    [, $order, $userVoucher] = placeOrderUsingVoucher();

    $admin = User::factory()->admin()->create();

    // lewat jalur 3: set pembayaran_gagal
    $this->actingAs($admin)
        ->patch("/office/orders/{$order->no_order}/status", ['status' => 'pembayaran_gagal']);

    expect($userVoucher->fresh()->status)->toBe('hangus');

    // pintu dipanggil ulang — status tetap hangus
    app(DiscountForfeitureService::class)->forfeitUnpaidCheckoutDiscounts($order->fresh());

    expect($userVoucher->fresh()->status)->toBe('hangus');
});
