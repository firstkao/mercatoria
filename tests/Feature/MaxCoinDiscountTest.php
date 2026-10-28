<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\User;
use App\Services\Order\ShippingCalculatorService;

test('maksimal diskon koin 5 persen — 17.000 diterima pada subtotal 340.000', function () {
    $service = app(ShippingCalculatorService::class);

    expect($service->maxCoinDiscount(340000))->toEqual(17000.0)
        ->and($service->maxCoinDiscount(255000))->toEqual(12750.0);
});

test('koin di atas 5 persen ditolak saat checkout', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 50000]);

    $itemA = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $itemB = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    // subtotal 340.000 → maks 17.000
    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id, $itemB->id],
        'discount_type' => 'coin',
        'coin_amount' => 17001,
    ]))->assertSessionHasErrors('coin_amount');

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$itemA->id, $itemB->id],
        'discount_type' => 'coin',
        'coin_amount' => 17000,
    ]))->assertRedirect();
});
