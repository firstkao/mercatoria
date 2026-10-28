<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;

function coinUser(int $balance): User
{
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => $balance]);

    return $user;
}

test('koin dipotong saat checkout dan dicatat used at checkout', function () {
    // subtotal 255.000 + 85.000 = 340.000 (contoh PRD 20.6)
    $user = coinUser(30000);
    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id, $itemB->id],
        'discount_type' => 'coin',
        'coin_amount' => 17000,
    ]))->assertRedirect();

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(13000);

    $tx = CoinTransaction::where('user_id', $user->id)->firstOrFail();
    expect($tx->type)->toBe('used_at_checkout')
        ->and($tx->amount)->toBe(-17000)
        ->and($tx->balance_after)->toBe(13000)
        ->and($tx->order_id)->not->toBeNull();
});

test('diskon koin dibatasi 5 persen subtotal', function () {
    $user = coinUser(100000);
    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);
    $ids = [$itemA->id, $itemB->id];

    // max = floor(340.000 × 5%) = 17.000 → 17.001 ditolak
    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => $ids,
        'discount_type' => 'coin',
        'coin_amount' => 17001,
    ]))->assertSessionHasErrors('coin_amount');

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => $ids,
        'discount_type' => 'coin',
        'coin_amount' => 17000,
    ]))->assertRedirect();

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(83000);
});

test('koin melebihi saldo ditolak', function () {
    $user = coinUser(10000);
    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id, $itemB->id],
        'discount_type' => 'coin',
        'coin_amount' => 15000,
    ]))->assertSessionHasErrors('coin_amount');

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(10000);
});

test('koin dan voucher tidak boleh dipakai bersamaan', function () {
    $user = coinUser(30000);
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $userVoucher = UserVoucher::factory()->create(['user_id' => $user->id, 'status' => 'active']);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'coin',
        'coin_amount' => 1000,
        'user_voucher_id' => $userVoucher->id,
    ]))->assertSessionHasErrors('user_voucher_id');
});

test('saldo koin pas untuk satu order saja — order kedua ditolak', function () {
    // max 5% × 255.000 = 12.750
    $user = coinUser(12750);
    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id],
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]))->assertRedirect();

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemB->id],
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]))->assertSessionHasErrors('coin_amount');

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});
