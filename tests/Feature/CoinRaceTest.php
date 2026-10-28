<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Cart\CheckoutService;
use App\Services\Order\OrderStatusService;
use App\Services\User\CoinWalletService;
use App\Services\User\DiscountForfeitureService;

test('dua referee menyelesaikan order — referrer menerima 3000 masing-masing', function () {
    $referrer = User::factory()->create();
    Coin::factory()->create(['user_id' => $referrer->id, 'balance' => 0]);

    $refereeA = User::factory()->create(['referred_by' => $referrer->id]);
    $refereeB = User::factory()->create(['referred_by' => $referrer->id]);

    $cartA = Cart::factory()->create(['user_id' => $refereeA->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $orderA = app(CheckoutService::class)->placeOrder($refereeA, [$cartA->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);

    $cartB = Cart::factory()->create(['user_id' => $refereeB->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $orderB = app(CheckoutService::class)->placeOrder($refereeB, [$cartB->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);

    // Serentak (berurutan dalam proses yang sama) — lockForUpdate menjaga saldo
    advanceToSelesai($orderA);
    advanceToSelesai($orderB);

    expect(Coin::where('user_id', $referrer->id)->value('balance'))->toBe(6000)
        ->and(CoinTransaction::where('user_id', $referrer->id)->where('type', 'earned_referral_referrer')->count())->toBe(2);
});

test('forfeit ganda tidak menggandakan baris dan tidak mengubah saldo', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    $forfeiture = app(DiscountForfeitureService::class);
    $statuses = app(OrderStatusService::class);

    $forfeiture->forfeitUnpaidCheckoutDiscounts($order);
    $statuses->changeStatus($order->fresh(), 'pembayaran_gagal', 'system');
    $forfeiture->forfeitUnpaidCheckoutDiscounts($order->fresh());

    expect(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->count())->toBe(1)
        ->and(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});

test('voucher yang sama dipakai dua order serentak — kedua percobaan aman', function () {
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'min_belanja' => 0, 'is_active' => true]);
    $userVoucher = UserVoucher::factory()->create([
        'user_id' => $user->id,
        'voucher_id' => $voucher->id,
        'status' => 'active',
    ]);

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

test('kredit koin berurutan ke saldo yang sama tidak korup', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 100]);

    $wallet = app(CoinWalletService::class);

    foreach ([3000, 3000, 1000] as $amount) {
        $wallet->credit($user, $amount, null, 'earned_referral_referrer', 'uji race');
    }

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(7100)
        ->and(CoinTransaction::where('user_id', $user->id)->count())->toBe(3);
});
