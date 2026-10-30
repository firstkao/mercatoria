<?php

use App\Models\Payment;

function proofFileFixture(\App\Models\Order $order, string $basename): string
{
    $path = 'payment-proofs/'.$basename;
    $dir = storage_path('app/private/payment-proofs');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(storage_path('app/private/'.$path), 'fake-proof-bytes');

    Payment::create([
        'order_id' => $order->id,
        'type' => 'fp',
        'method' => $order->payment_method,
        'amount' => $order->tagihan_web,
        'file_path' => $path,
        'status' => 'verified',
        'uploaded_at' => now(),
        'verified_at' => now(),
    ]);

    return $path;
}

test('file bukti transfer terhapus saat order selesai', function () {
    [$user, $order] = placeTestOrder();
    $path = proofFileFixture($order, 'bukti-selesai.jpg');
    expect(file_exists(storage_path('app/private/'.$path)))->toBeTrue();

    advanceToSelesai($order);
    $this->artisan('payments:purge-proofs')->assertSuccessful();

    expect(file_exists(storage_path('app/private/'.$path)))->toBeFalse();
});

test('file bukti transfer tetap untuk order aktif', function () {
    [$user, $order] = placeTestOrder();
    $path = proofFileFixture($order, 'bukti-aktif.jpg');

    // Order tetap Menunggu Pembayaran (aktif) â€” file tidak boleh tersentuh.
    $this->artisan('payments:purge-proofs')->assertSuccessful();

    expect($order->fresh()->status)->toBe('menunggu_pembayaran')
        ->and(file_exists(storage_path('app/private/'.$path)))->toBeTrue();
});
