<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Refund;
use App\Models\User;
use App\Services\Cart\CheckoutService;
use App\Services\Order\LatePenaltyService;
use App\Services\Order\OrderStatusService;
use App\Services\User\UserTierService;

function tierService(): UserTierService
{
    return app(UserTierService::class);
}

test('order masuk sedang diproses menghitung total belanja full termasuk dp', function () {
    $user = User::factory()->create();

    // Order DP 255.000 (tagihan web 127.500) — PRD 50: "nilai penuh pesanan (orders.total)"
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $order = app(CheckoutService::class)->placeOrder($user, [$cart->id], [
        'platform' => 'toco', 'scheme' => 'dp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);

    $statuses = app(OrderStatusService::class);
    $statuses->changeStatus($order, 'ditahan', 'user');
    $statuses->changeStatus($order, 'pembayaran_diterima', 'admin');

    // Pembayaran Diterima belum dihitung (PRD 50)
    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(0.0);

    $statuses->changeStatus($order, 'sedang_diproses', 'admin');

    $customer = Customer::where('user_id', $user->id)->firstOrFail();
    expect((float) $customer->total_belanja)->toEqual(255000.0)
        ->and($order->fresh()->tier_counted_at)->not->toBeNull()
        ->and((float) $order->fresh()->tier_counted_amount)->toEqual(255000.0);
});

test('hitungan tier idempotent — tidak dobel', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }

    tierService()->countOrder($order);

    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(255000.0);
});

test('refund full dan hangus mengurangi total belanja', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }

    $statuses->changeStatus($order, 'dana_dikembalikan', 'admin');

    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(0.0);

    // Kasus hangus: hitung lagi lalu hangus
    [$user2, $order2] = placeTestOrder($user);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan'] as $s) {
        $statuses->changeStatus($order2, $s, 'admin');
    }
    $statuses->changeStatus($order2, 'hangus', 'system');

    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(0.0);
});

test('partial refund mengurangi tier_counted_amount sesuai nominal refund', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }

    expect((float) $order->fresh()->tier_counted_amount)->toEqual(255000.0);

    Refund::create([
        'order_id' => $order->id,
        'type' => Refund::TYPE_PARTIAL,
        'amount' => 50000,
        'reason' => 'kualitas barang',
        'status' => Refund::STATUS_COMPLETED,
        'requested_by' => User::factory()->admin()->create()->id,
        'completed_at' => now(),
    ]);

    $statuses->changeStatus($order, 'dana_dikembalikan', 'admin');

    expect((float) $order->fresh()->tier_counted_amount)->toEqual(205000.0)
        ->and((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(205000.0);
});

test('denda lunas mengembalikan nilai order tetapi denda tidak dihitung tier', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(3)]);

    $penalty = app(LatePenaltyService::class);
    $penalty->unlockViaPenalty($order, User::factory()->admin()->create(), 3000);

    // Denda dibayar → nilai order kembali ke total belanja (PRD 50)
    $penalty->markPaid($order);

    $customer = Customer::where('user_id', $user->id)->firstOrFail();
    expect((float) $customer->total_belanja)->toEqual(255000.0);
});

test('tier berekapulasi ke gold dan platinum sesuai threshold', function () {
    $user = User::factory()->create();

    tierService()->applyTier($user, 2500000);
    expect(Customer::where('user_id', $user->id)->value('tier'))->toBe('gold');

    tierService()->applyTier($user, 5500000);
    expect(Customer::where('user_id', $user->id)->value('tier'))->toBe('platinum');

    tierService()->applyTier($user, 500000);
    expect(Customer::where('user_id', $user->id)->value('tier'))->toBe('silver');
});
