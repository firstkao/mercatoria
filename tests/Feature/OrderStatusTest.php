<?php

use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\UploadedFile;

function statusService(): OrderStatusService
{
    return app(OrderStatusService::class);
}

test('transisi valid berantai sampai selesai', function () {
    [, $order] = placeTestOrder();

    $chain = [
        'ditahan',
        'pembayaran_diterima',
        'sedang_diproses',
        'sampai_wh_china',
        'dikirim_ke_indonesia',
        'bea_cukai',
        'sampai_wh_indonesia',
        'menunggu_pelunasan_pengambilan',
        'sedang_dikemas',
        'selesai',
    ];

    foreach ($chain as $status) {
        statusService()->changeStatus($order, $status, 'admin');
    }

    expect($order->fresh()->status)->toBe('selesai');
});

test('transisi ilegal ditolak', function () {
    [, $order] = placeTestOrder();

    // Menunggu Pembayaran tidak boleh langsung ke Selesai
    expect(fn () => statusService()->changeStatus($order, 'selesai', 'admin'))
        ->toThrow(Exception::class);

    expect($order->fresh()->status)->toBe('menunggu_pembayaran');

    statusService()->changeStatus($order, 'ditahan', 'user');
    statusService()->changeStatus($order, 'pembayaran_diterima', 'admin');
    statusService()->changeStatus($order, 'sedang_diproses', 'admin');

    // Sudah maju tidak boleh mundur ke Ditahan
    expect(fn () => statusService()->changeStatus($order, 'ditahan', 'admin'))
        ->toThrow(Exception::class);
});

test('setiap transisi menulis order status log', function () {
    [, $order] = placeTestOrder();

    statusService()->changeStatus($order, 'ditahan', 'user');

    $log = OrderStatusLog::where('order_id', $order->id)->latest('id')->firstOrFail();
    expect($log->status)->toBe('ditahan')
        ->and($log->changed_by)->toBe('user');
});

test('admin menolak bukti â†’ kembali menunggu pembayaran dan deadline direset', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe('ditahan');

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $oldDeadline = $order->fresh()->payment_deadline_at;

    $this->travel(2)->hours();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/reject")
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('menunggu_pembayaran')
        ->and($payment->fresh()->status)->toBe('rejected')
        ->and($order->payment_deadline_at->greaterThan($oldDeadline))->toBeTrue();
});

test('admin verifikasi bukti â†’ pembayaran diterima', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/verify")
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('pembayaran_diterima')
        ->and($payment->fresh()->status)->toBe('verified');
});

test('admin dapat mengubah status lewat endpoint office', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)
        ->patch("/office/orders/{$order->no_order}/status", ['status' => 'ditahan'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('ditahan');
});

test('refund ke dana dikembalikan hanya dari status yang sah', function () {
    [, $order] = placeTestOrder();

    // Masih Menunggu Pembayaran belum boleh di-refund
    expect(fn () => statusService()->changeStatus($order, 'dana_dikembalikan', 'admin'))
        ->toThrow(Exception::class);

    statusService()->changeStatus($order, 'ditahan', 'user');
    statusService()->changeStatus($order, 'pembayaran_diterima', 'admin');

    statusService()->changeStatus($order, 'dana_dikembalikan', 'admin');

    expect($order->fresh()->status)->toBe('dana_dikembalikan');
});
