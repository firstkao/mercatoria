<?php

use App\Models\AdminLog;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserVoucher;
use App\Services\Payment\RefundService;
use App\Services\SplitBox\SplitBoxService;
use App\Services\User\UserNotificationService;

function refundReady(array $orderPair): array
{
    [$user, $order] = $orderPair;
    $order->update(['status' => 'ditahan']);

    return [$user, $order];
}

test('requestFull membuat refund requested dan notif', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeTestOrder());

    $refund = app(RefundService::class)->requestFull($order, 'Semua barang kosong', $admin);

    expect($refund->status)->toBe('requested')
        ->and($refund->type)->toBe('full')
        ->and((float) $refund->amount)->toBe((float) $order->tagihan_web)
        ->and($refund->reason)->toBe('Semua barang kosong')
        ->and($refund->requested_by)->toBe($admin->id);

    $notif = UserNotification::where('user_id', $user->id)->where('type', 'refund_requested')->firstOrFail();
    expect($notif->title)->toContain('Pengembalian dana pesanan #'.$order->no_order)
        ->toContain('sedang diproses');
});

test('requestPartial menyimpan amount benar dan notif', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeTestOrder());

    $refund = app(RefundService::class)->requestPartial($order, 100000, '1 dari 2 barang kosong', $admin);

    expect($refund->status)->toBe('requested')
        ->and($refund->type)->toBe('partial')
        ->and((float) $refund->amount)->toBe(100000.0)
        ->and(UserNotification::where('user_id', $user->id)->where('type', 'refund_requested')->exists())->toBeTrue();
});

test('complete mengembalikan koin dan mengubah order jadi dana_dikembalikan', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeOrderUsingCoin(12750));

    $refund = app(RefundService::class)->requestFull($order, 'Kosong', $admin);
    app(RefundService::class)->complete($refund, $admin);

    $order->refresh();
    $refund->refresh();

    expect($refund->status)->toBe('completed')
        ->and($refund->completed_at)->not->toBeNull()
        ->and($refund->handled_by)->toBe($admin->id)
        ->and($order->status)->toBe('dana_dikembalikan');

    // Koin kembali penuh ke wallet
    expect((int) Coin::where('user_id', $user->id)->value('balance'))->toBe(12750)
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'refund_returned')->exists())->toBeTrue();

    $notif = UserNotification::where('user_id', $user->id)->where('type', 'refund_completed')->firstOrFail();
    expect($notif->title)->toContain('selesai')
        ->toContain('Koin');
});

test('complete mengembalikan voucher ke active', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order, $userVoucher] = placeOrderUsingVoucher();
    $order->update(['status' => 'ditahan']);

    $refund = app(RefundService::class)->requestFull($order, 'Kosong', $admin);
    app(RefundService::class)->complete($refund, $admin);

    $userVoucher->refresh();

    expect($userVoucher->status)->toBe(UserVoucher::STATUS_ACTIVE)
        ->and($userVoucher->used_at)->toBeNull()
        ->and($userVoucher->status)->not->toBe(UserVoucher::STATUS_HANGUS);

    $notif = UserNotification::where('user_id', $user->id)->where('type', 'refund_completed')->firstOrFail();
    expect($notif->title)->toContain('Voucher');
});

test('reject menyimpan alasan, notif, dan order tidak berubah', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeTestOrder());

    $refund = app(RefundService::class)->requestFull($order, 'Kosong', $admin);
    app(RefundService::class)->reject($refund, 'Barang sudah dikirim', $admin);

    $refund->refresh();
    $order->refresh();

    expect($refund->status)->toBe('rejected')
        ->and($refund->rejection_reason)->toBe('Barang sudah dikirim')
        ->and($refund->handled_by)->toBe($admin->id)
        ->and($order->status)->toBe('ditahan');

    $notif = UserNotification::where('user_id', $user->id)->where('type', 'refund_rejected')->firstOrFail();
    expect($notif->title)->toContain('ditolak')
        ->toContain('Barang sudah dikirim');
});

test('split box refund otomatis membuat refund completed dan satu notif', function () {
    $admin = User::factory()->admin()->create();
    $f = splitBoxFixture();
    Coin::factory()->create(['user_id' => $f['user']->id, 'balance' => 100]);
    [$user, $order] = placeSplitBoxOrder($f['user'], $f['product'], [$f['hot'], $f['normalA']], 1, [
        'discount_type' => 'coin',
        'coin_amount' => 100,
    ]);
    $order->update(['status' => 'ditahan']);

    // Kunci item ke batch slot (kondisi nyata: terjadi saat upload bukti)
    foreach ($order->items as $item) {
        $slot = collect([$f['hot'], $f['normalA'], $f['normalB']])->firstWhere('variant_id', $item->variant_id);
        $batchSlot = SplitBoxBatchSlot::where('batch_id', $f['batch']->id)->where('slot_id', $slot->id)->firstOrFail();
        $item->update(['split_box_batch_slot_id' => $batchSlot->id]);
    }

    app(SplitBoxService::class)->closeBatchAndRefund($f['batch'], $admin);

    $refund = Refund::where('order_id', $order->id)->sole();

    expect($refund->type)->toBe('full')
        ->and($refund->status)->toBe('completed')
        ->and($refund->reason)->toBe('Slot split box tidak terpenuhi')
        ->and($refund->completed_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe('dana_dikembalikan');

    // Koin kembali + hanya 1 notif refund (langsung completed)
    expect((int) Coin::where('user_id', $user->id)->value('balance'))->toBe(100)
        ->and(UserNotification::where('user_id', $user->id)->where('type', 'like', 'refund%')->count())->toBe(1)
        ->and(UserNotification::where('user_id', $user->id)->where('type', 'refund_completed')->exists())->toBeTrue();
});

test('pengembalian koin voucher tidak memicu jalur forfeit', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeOrderUsingCoin(12750));

    $refund = app(RefundService::class)->requestFull($order, 'Kosong', $admin);
    app(RefundService::class)->complete($refund, $admin);

    // Forfeit = TYPE_FORFEIT_AT_CHECKOUT / TYPE_HANGUS_PAYMENT_FAILED — tidak boleh ada
    expect(CoinTransaction::where('order_id', $order->id)->where('type', 'forfeit_at_checkout')->exists())->toBeFalse()
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'hangus_payment_failed')->exists())->toBeFalse()
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'refund_returned')->exists())->toBeTrue();
});

test('rollback saat gagal di tengah: koin dan status tidak berubah', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeOrderUsingCoin(12750));
    $refund = app(RefundService::class)->requestFull($order, 'Kosong', $admin);

    // Notif akhir melempar exception → SELURUH transaksi wajib batal
    $this->mock(UserNotificationService::class, function ($mock) {
        $mock->shouldReceive('notify')->andReturnUsing(function ($u, $type) {
            if ($type === 'refund_completed') {
                throw new RuntimeException('gagal di tengah');
            }

            return new UserNotification;
        });
    });

    try {
        app(RefundService::class)->complete($refund, $admin);
    } catch (Throwable) {
    }

    // Semua tulisan DB harus berguling: saldo koin, transaksi, status refund & order
    expect((int) Coin::where('user_id', $user->id)->value('balance'))->toBe(0)
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'refund_returned')->exists())->toBeFalse()
        ->and($refund->fresh()->status)->toBe('requested')
        ->and($refund->fresh()->completed_at)->toBeNull()
        ->and($order->fresh()->status)->toBe('ditahan');
});

test('endpoint refund admin mencatat admin_logs dan non-admin ditolak 403', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = refundReady(placeTestOrder());
    Payment::factory()->create(['order_id' => $order->id, 'amount' => 100000, 'status' => Payment::STATUS_VERIFIED]);

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/refund", [
        'reason' => 'Barang kosong semua',
    ])->assertRedirect();

    $refund = Refund::where('order_id', $order->id)->sole();

    $this->actingAs($admin)->post("/office/refunds/{$refund->id}/reject", [
        'rejection_reason' => 'Tidak memenuhi syarat',
    ])->assertRedirect();

    // request ulang lalu complete
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/refund/partial", [
        'amount' => 50000,
        'reason' => 'Sebagian kosong',
    ])->assertRedirect();
    $partial = Refund::where('order_id', $order->id)->where('type', 'partial')->sole();

    $this->actingAs($admin)->post("/office/refunds/{$partial->id}/complete")->assertRedirect();

    $logs = AdminLog::where('model', 'Refund');
    expect($logs->count())->toBe(4)
        ->and($logs->pluck('action')->all())->toContain('refund_full', 'refund_partial', 'refund_complete', 'refund_reject');

    $sample = $logs->first();
    expect($sample->new_value)->toHaveKeys(['order_id', 'refund_id', 'amount']);

    // Non-admin 403 semua endpoint
    $customer = User::factory()->create();
    $this->actingAs($customer)->post("/office/orders/{$order->no_order}/refund", ['reason' => 'x'])->assertForbidden();
    $this->actingAs($customer)->post("/office/orders/{$order->no_order}/refund/partial", ['amount' => 1, 'reason' => 'x'])->assertForbidden();
    $this->actingAs($customer)->post("/office/refunds/{$partial->id}/complete")->assertForbidden();
    $this->actingAs($customer)->post("/office/refunds/{$partial->id}/reject", ['rejection_reason' => 'x'])->assertForbidden();
});
