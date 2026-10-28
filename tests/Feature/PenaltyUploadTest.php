<?php

use App\Models\Payment;
use App\Models\User;
use App\Services\Order\LatePenaltyService;
use Illuminate\Http\UploadedFile;

test('bukti denda diupload dengan type penalty', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);
    app(LatePenaltyService::class)->unlockViaPenalty($order, $admin);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-denda.jpg'),
    ])->assertRedirect();

    $payment = Payment::where('order_id', $order->id)->where('type', 'penalty')->firstOrFail();
    expect($payment->status)->toBe('pending');
});

test('verifikasi bukti denda mengembalikan order ke menunggu pelunasan', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);
    app(LatePenaltyService::class)->unlockViaPenalty($order, $admin);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-denda.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->where('type', 'penalty')->firstOrFail();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/verify");

    $order->refresh();
    expect($order->status)->toBe('menunggu_pelunasan_pengambilan')
        ->and($order->pickup_deadline_at)->not->toBeNull();
});

test('upload ditolak bila bukan status menunggu denda', function () {
    [$user, $order] = placeTestOrder(); // menunggu_pembayaran

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // Order sudah ditahan (lock 1x) — tidak bisa kirim penalty sembarangan
    expect(Payment::where('type', 'penalty')->exists())->toBeFalse();
});
