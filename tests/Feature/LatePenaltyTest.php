<?php

use App\Models\User;
use App\Services\Order\LatePenaltyService;

function penaltyService(): LatePenaltyService
{
    return app(LatePenaltyService::class);
}

test('rumus denda per hari dibulatkan ke atas minimal 1 hari', function () {
    // PRD 51.1: late_penalty_per_day (1.000) × hari sejak hangus_at, dibulatkan ke atas, min 1 hari
    [$user, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(3)]);

    expect(penaltyService()->calculate($order))->toBe(3000);

    $order->update(['hangus_at' => now()->subHours(1)]);
    expect(penaltyService()->calculate($order))->toBe(1000); // min 1 hari

    $order->update(['hangus_at' => now()->subHours(36)]);
    expect(penaltyService()->calculate($order))->toBe(2000); // 1,5 hari → 2 hari
});

test('buka kunci via denda membekukan nominal dan ke menunggu denda', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    penaltyService()->unlockViaPenalty($order, $admin);

    $order->refresh();
    expect($order->status)->toBe('menunggu_denda')
        ->and((float) $order->penalty_amount)->toEqual(2000.0)
        ->and($order->penalty_deadline_at)->not->toBeNull();
});

test('admin boleh mengubah nominal denda', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    penaltyService()->unlockViaPenalty($order, $admin, 7500);

    expect((float) $order->fresh()->penalty_amount)->toEqual(7500.0);
});

test('denda lunas kembali ke menunggu pelunasan dengan timer co baru', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    penaltyService()->unlockViaPenalty($order, $admin);
    penaltyService()->markPaid($order);

    $order->refresh();
    expect($order->status)->toBe('menunggu_pelunasan_pengambilan')
        ->and($order->pickup_deadline_at)->not->toBeNull()
        ->and($order->penalty_paid_at)->not->toBeNull();
});

test('denda tidak dibayar sampai batas kembali ke hangus', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    penaltyService()->unlockViaPenalty($order, $admin);
    $order->update(['penalty_deadline_at' => now()->subHour()]);

    $this->artisan('orders:expire-penalties')->assertSuccessful();

    expect($order->fresh()->status)->toBe('hangus');
});

test('admin dapat menetapkan hangus permanen', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()]);

    penaltyService()->markHangusPermanen($order, $admin);

    expect($order->fresh()->status)->toBe('hangus_permanen');
});

test('hangus permanen mengisi kolom hangus_permanent_at', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()]);

    penaltyService()->markHangusPermanen($order, $admin);

    expect($order->fresh()->hangus_permanent_at)->not->toBeNull();
});
