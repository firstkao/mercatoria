<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Services\User\DiscountForfeitureService;

test('tidak ada kode yang mengembalikan koin setelah forfeit', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    $balanceSetelahDebit = Coin::where('user_id', $user->id)->value('balance');
    $saldoSnapshot = $balanceSetelahDebit;

    // Seluruh pintu yang ada dipanggil ulang — saldo harus tetap
    app(DiscountForfeitureService::class)->forfeitUnpaidCheckoutDiscounts($order->fresh());
    app(DiscountForfeitureService::class)->forfeitUnpaidCheckoutDiscounts($order->fresh());

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe($saldoSnapshot)
        ->and($balanceSetelahDebit)->toBe(0);

    // Tidak ada baris credit/earn untuk order tersebut
    expect(CoinTransaction::where('order_id', $order->id)
        ->where('type', 'like', 'earned_%')
        ->exists())->toBeFalse();
});

test('baris forfeit tidak menambah saldo dan mencatat angka hangus', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    $forfeit = CoinTransaction::where('order_id', $order->id)
        ->where('type', 'forfeit_at_checkout')
        ->firstOrFail();

    expect($forfeit->amount)->toBe(0)
        ->and($forfeit->balance_after)->toBe(0)
        ->and($forfeit->note)->toContain($order->no_order);
});

test('transaksi used at checkout tetap tersimpan sebagai audit', function () {
    [$user, $order] = placeOrderUsingCoin(12750);

    $used = CoinTransaction::where('order_id', $order->id)
        ->where('type', 'used_at_checkout')
        ->firstOrFail();

    expect($used->amount)->toBe(-12750);

    $order->update(['payment_deadline_at' => now()->subHour()]);
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    // Row USED_AT_CHECKOUT tidak dihapus/diubah jadi positif
    expect($used->fresh()->amount)->toBe(-12750)
        ->and(CoinTransaction::where('order_id', $order->id)->count())->toBe(2);
});
