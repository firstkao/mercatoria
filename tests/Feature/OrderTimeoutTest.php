<?php

use Illuminate\Http\UploadedFile;

test('order lewat 24 jam tanpa bayar menjadi pembayaran gagal', function () {
    [, $order] = placeTestOrder();
    $order->update(['payment_deadline_at' => now()->subHour()]);

    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe('pembayaran_gagal');
});

test('order yang belum lewat batas tidak diubah', function () {
    [, $order] = placeTestOrder();
    $order->update(['payment_deadline_at' => now()->addHours(2)]);

    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('order ditahan dengan bukti pending tidak ikut hangus saat lewat 24 jam pertama', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $order->update(['payment_deadline_at' => now()->subHour()]);

    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    // Menunggu verifikasi admin â€” belum dianggap gagal
    expect($order->fresh()->status)->toBe('ditahan');
});

test('order lewat 7 hari di menunggu pelunasan menjadi hangus', function () {
    [, $order] = placeTestOrder();
    $order->update([
        'status' => 'menunggu_pelunasan_pengambilan',
        'pickup_deadline_at' => now()->subDay(),
    ]);

    $this->artisan('orders:hangus-expired')->assertSuccessful();

    expect($order->fresh()->status)->toBe('hangus');
});

test('order menunggu pelunasan yang belum 7 hari tetap', function () {
    [, $order] = placeTestOrder();
    $order->update([
        'status' => 'menunggu_pelunasan_pengambilan',
        'pickup_deadline_at' => now()->addDays(3),
    ]);

    $this->artisan('orders:hangus-expired')->assertSuccessful();

    expect($order->fresh()->status)->toBe('menunggu_pelunasan_pengambilan');
});
