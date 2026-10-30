<?php

use App\Models\Customer;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use App\Services\Payment\RefundService;

function tierReversalSetup(): array
{
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }

    return [$user, $order];
}

test('partial refund membalikkan tier sesuai nominal bukan penuh', function () {
    [$user, $order] = tierReversalSetup();
    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(255000.0);

    $admin = User::factory()->admin()->create();
    $refund = app(RefundService::class)->requestPartial($order, 85000.0, '1 dari 2 barang kosong', $admin);
    app(RefundService::class)->complete($refund, $admin);

    // PRD 50: partial refund â†’ kurangi nominal yang di-refund (85.000), bukan seluruh 255.000.
    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(170000.0)
        ->and((float) Customer::where('user_id', $user->id)->value('total_belanja'))->not->toEqual(0.0);
});

test('full refund via refund service tetap membalikkan penuh', function () {
    [$user, $order] = tierReversalSetup();
    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(255000.0);

    $admin = User::factory()->admin()->create();
    $refund = app(RefundService::class)->requestFull($order, 'Semua barang kosong', $admin);
    app(RefundService::class)->complete($refund, $admin);

    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(0.0);
});
