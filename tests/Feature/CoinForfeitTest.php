<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use App\Services\User\DiscountForfeitureService;
use Illuminate\Http\UploadedFile;

test('jalur 1 — timeout 24 jam membuat koin hangus tanpa pengembalian', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe('pembayaran_gagal');

    $forfeit = CoinTransaction::where('order_id', $order->id)
        ->where('type', 'forfeit_at_checkout')
        ->firstOrFail();

    expect($forfeit->amount)->toBe(0)
        ->and($forfeit->user_id)->toBe($user->id);

    // Saldo TIDAK dikembalikan
    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});

test('jalur 2 — ditolak lalu tidak upload ulang sampai timeout hangus', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeOrderUsingCoin(12750);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/reject");

    // Tidak upload ulang sampai lewat deadline
    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe('pembayaran_gagal')
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});

test('jalur 3 — pembayaran_gagal manual tanpa payment verified hangus', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeOrderUsingCoin(12750);

    $this->actingAs($admin)
        ->patch("/office/orders/{$order->no_order}/status", ['status' => 'pembayaran_gagal']);

    expect($order->fresh()->status)->toBe('pembayaran_gagal')
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});

test('jalur 4 — slot sold memicu hangus koin (hook bagian g)', function () {
    [, $order] = placeOrderUsingCoin(12750);

    // Pola pemanggilan yang akan dipakai SplitBoxService di (g):
    app(OrderStatusService::class)
        ->changeStatus($order, 'pembayaran_gagal', 'system', 'slot_sold');

    expect($order->fresh()->status)->toBe('pembayaran_gagal')
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->exists())->toBeTrue();
});

test('pintu forfeit idempoten — dipanggil dua kali tetap satu baris', function () {
    [, $order] = placeOrderUsingCoin(12750);

    $forfeiture = app(DiscountForfeitureService::class);
    $forfeiture->forfeitUnpaidCheckoutDiscounts($order);
    $forfeiture->forfeitUnpaidCheckoutDiscounts($order);

    expect(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->count())->toBe(1);
});

test('hangus 7 hari juga mematikan koin (prd 9.3 maupun hangus)', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    $order->update([
        'status' => 'menunggu_pelunasan_pengambilan',
        'pickup_deadline_at' => now()->subDay(),
    ]);

    $this->artisan('orders:hangus-expired')->assertSuccessful();

    expect($order->fresh()->status)->toBe('hangus')
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $user->id)->value('balance'))->toBe(0);
});
