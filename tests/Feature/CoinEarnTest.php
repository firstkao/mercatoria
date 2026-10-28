<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Services\Cart\CheckoutService;
use App\Services\Order\OrderStatusService;

test('referral 3000 koin ke referrer saat referee selesaikan order pertama', function () {
    $referrer = User::factory()->create();
    Coin::factory()->create(['user_id' => $referrer->id, 'balance' => 0]);

    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    $cart = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $order = app(CheckoutService::class)->placeOrder($referee, [$cart->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);

    advanceToSelesai($order);

    expect(Coin::where('user_id', $referrer->id)->value('balance'))->toBe(3000);

    $tx = CoinTransaction::where('user_id', $referrer->id)
        ->where('type', 'earned_referral_referrer')
        ->firstOrFail();

    expect($tx->amount)->toBe(3000)
        ->and($tx->order_id)->toBe($order->id)
        ->and($tx->expired_at)->not->toBeNull();
});

test('referral 1000 koin ke referee saat pembayaran pertama terverifikasi', function () {
    $referrer = User::factory()->create();
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Coin::factory()->create(['user_id' => $referee->id, 'balance' => 0]);

    $cart = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $order = app(CheckoutService::class)->placeOrder($referee, [$cart->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);

    $statuses = app(OrderStatusService::class);
    $statuses->changeStatus($order, 'ditahan', 'user');
    $statuses->changeStatus($order, 'pembayaran_diterima', 'admin');

    expect(Coin::where('user_id', $referee->id)->value('balance'))->toBe(1000);

    $tx = CoinTransaction::where('user_id', $referee->id)
        ->where('type', 'earned_referral_referee')
        ->firstOrFail();

    expect($tx->amount)->toBe(1000);
});

test('koin 1 persen dari orders.total saat selesai (floor)', function () {
    [$user, $order] = placeTestOrder(); // total 255.000

    advanceToSelesai($order);

    // 1% × 255.000 = 2.550
    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(2550);

    $tx = CoinTransaction::where('user_id', $user->id)->where('type', 'earned_order')->firstOrFail();
    expect($tx->amount)->toBe(2550)
        ->and($tx->order_id)->toBe($order->id);
});

test('bonus referral hanya untuk order pertama referee', function () {
    $referrer = User::factory()->create();
    Coin::factory()->create(['user_id' => $referrer->id, 'balance' => 0]);
    $referee = User::factory()->create(['referred_by' => $referrer->id]);

    $cartA = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $orderA = app(CheckoutService::class)->placeOrder($referee, [$cartA->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);
    advanceToSelesai($orderA);

    $cartB = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal85()->id, 'qty' => 1]);
    $orderB = app(CheckoutService::class)->placeOrder($referee, [$cartB->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);
    advanceToSelesai($orderB);

    // 3.000 hanya sekali; 1% tetap tiap order selesai (255.000 + 85.000 = 3.400 untuk referee)
    expect(Coin::where('user_id', $referrer->id)->value('balance'))->toBe(3000)
        ->and(CoinTransaction::where('user_id', $referrer->id)->where('type', 'earned_referral_referrer')->count())->toBe(1);
});

test('bonus 1000 hanya untuk pembayaran pertama referee', function () {
    $referrer = User::factory()->create();
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Coin::factory()->create(['user_id' => $referee->id, 'balance' => 0]);

    $statuses = app(OrderStatusService::class);

    $cartA = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $orderA = app(CheckoutService::class)->placeOrder($referee, [$cartA->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);
    $statuses->changeStatus($orderA, 'ditahan', 'user');
    $statuses->changeStatus($orderA, 'pembayaran_diterima', 'admin');

    $cartB = Cart::factory()->create(['user_id' => $referee->id, 'product_id' => productFinal85()->id, 'qty' => 1]);
    $orderB = app(CheckoutService::class)->placeOrder($referee, [$cartB->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);
    $statuses->changeStatus($orderB, 'ditahan', 'user');
    $statuses->changeStatus($orderB, 'pembayaran_diterima', 'admin');

    expect(Coin::where('user_id', $referee->id)->value('balance'))->toBe(1000)
        ->and(CoinTransaction::where('user_id', $referee->id)->where('type', 'earned_referral_referee')->count())->toBe(1);
});

test('self referral ditolak saat register', function () {
    $referrer = User::factory()->create([
        'referral_code' => 'MER-REF-9999',
        'no_hp' => '6281234567890',
    ]);

    $this->post('/register', registerPayload([
        'email' => 'kembar@example.com',
        'no_hp' => '081234567890',
        'referral_code' => 'MER-REF-9999',
    ]))->assertSessionHasErrors('referral_code');

    expect(User::where('email', 'kembar@example.com')->exists())->toBeFalse();
});
