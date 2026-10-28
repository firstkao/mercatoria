<?php

use App\Models\Payment;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\UploadedFile;

function slotTaken(string $slotId): int
{
    return (int) SplitBoxBatchSlot::where('slot_id', $slotId)->sum('qty_taken');
}

test('bukti ditolak â†’ slot dilepas', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);
    expect(slotTaken($fx['hot']->id))->toBe(1);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/reject");

    expect(slotTaken($fx['hot']->id))->toBe(0)
        ->and($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('pembayaran gagal dan refund â†’ slot dilepas', function () {
    $fx = splitBoxFixture();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);
    expect(slotTaken($fx['hot']->id))->toBe(1);

    $statuses = app(OrderStatusService::class);
    $statuses->changeStatus($order, 'ditahan', 'user');
    $statuses->changeStatus($order, 'pembayaran_gagal', 'system');

    expect(slotTaken($fx['hot']->id))->toBe(0);
});

test('hangus â†’ slot tidak dilepas', function () {
    $fx = splitBoxFixture();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }
    $statuses->changeStatus($order, 'hangus', 'system');

    expect(slotTaken($fx['hot']->id))->toBe(1);
});

test('refund setelah lock â†’ slot dilepas', function () {
    $fx = splitBoxFixture();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    $statuses = app(OrderStatusService::class);
    $statuses->changeStatus($order, 'ditahan', 'user');
    $statuses->changeStatus($order, 'pembayaran_diterima', 'admin');
    $statuses->changeStatus($order, 'dana_dikembalikan', 'admin');

    expect(slotTaken($fx['hot']->id))->toBe(0);
});
