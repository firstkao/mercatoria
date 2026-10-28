<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;

function voucherUser(array $voucherOverrides = []): array
{
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(array_merge([
        'nominal' => 25000,
        'min_belanja' => 100000,
        'is_active' => true,
    ], $voucherOverrides));
    $userVoucher = UserVoucher::factory()->create([
        'user_id' => $user->id,
        'voucher_id' => $voucher->id,
        'status' => 'active',
    ]);

    return [$user, $userVoucher];
}

test('voucher memotong harga sesuai nominal', function () {
    [$user, $userVoucher] = voucherUser();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertRedirect();

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect((float) $order->discount_amount)->toEqual(25000.0)
        ->and((float) $order->total)->toEqual(230000.0);

    expect($userVoucher->fresh()->status)->toBe('used_at_checkout')
        ->and($userVoucher->fresh()->order_id)->toBe($order->id);
});

test('voucher ditolak bila subtotal kurang dari min belanja', function () {
    [$user, $userVoucher] = voucherUser(['min_belanja' => 100000]);
    // subtotal 85.000 < 100.000
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');

    expect($userVoucher->fresh()->status)->toBe('active');
});

test('voucher nonaktif ditolak', function () {
    [$user, $userVoucher] = voucherUser(['is_active' => false]);
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');
});

test('voucher kedaluwarsa ditolak', function () {
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'min_belanja' => 0, 'is_active' => true]);
    $userVoucher = UserVoucher::factory()->create([
        'user_id' => $user->id,
        'voucher_id' => $voucher->id,
        'status' => 'active',
        'expires_at' => now()->subDay(),
    ]);
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');
});

test('voucher dipakai dua kali — percobaan kedua ditolak', function () {
    [$user, $userVoucher] = voucherUser();
    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertRedirect();

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemB->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');

    expect($userVoucher->fresh()->status)->toBe('used_at_checkout');
});

test('voucher milik user lain tidak bisa dipakai', function () {
    $user = User::factory()->create();
    [$other, $otherVoucher] = voucherUser();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'voucher',
        'user_voucher_id' => $otherVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');
});
