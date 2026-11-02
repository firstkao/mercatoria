<?php

use App\Enums\SplitRole;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use App\Services\Order\LatePenaltyService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Facades\Schema;

function addBatchWithStock(array $fx, int $batchNo, int $stock): SplitBoxBatch
{
    $batch = SplitBoxBatch::create(['split_box_id' => $fx['splitBox']->id, 'batch_no' => $batchNo, 'status' => 'open']);

    foreach ([$fx['hot'], $fx['normalA'], $fx['normalB']] as $slot) {
        SplitBoxBatchSlot::create(['batch_id' => $batch->id, 'slot_id' => $slot->id, 'qty_total' => $stock, 'qty_taken' => 0]);
    }

    return $batch;
}

test('FIFO memakai batch_no terkecil walau batch_id lebih besar', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['batch']->update(['batch_no' => 5]);
    $low = addBatchWithStock($fx, 2, 1);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['normalA']]);

    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeTrue();

    $item = $order->items()->first();
    expect($item->splitBoxBatchSlot->batch_id)->toBe($low->id);
});

test('item tidak cukup di satu kloter manapun gagal utuh', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    addBatchWithStock($fx, 2, 1);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['normalA']], qty: 2);

    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeFalse()
        ->and((int) SplitBoxBatchSlot::where('slot_id', $fx['normalA']->id)->sum('qty_taken'))->toBe(0);
});

test('pair boleh lintas kloter', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['batch']->batchSlots()->where('slot_id', $fx['normalA']->id)->update(['qty_taken' => 1]);
    $b2 = addBatchWithStock($fx, 2, 1);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeTrue();

    $batchIds = $order->items()->get()->map(fn ($i) => $i->splitBoxBatchSlot->batch_id)->all();
    expect($batchIds)->toContain($fx['batch']->id, $b2->id);
});

test('closed_at terisi saat kloter ditutup dan opened_at saat auto-open', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    app(SplitBoxService::class)->closeBatchAndRefund($fx['batch'], $admin);
    expect($fx['batch']->fresh()->closed_at)->not->toBeNull();

    $fx2 = splitBoxFixture(stockPerSlot: 1);
    $fx2['batch']->batchSlots()->update(['qty_taken' => 1]);
    app(SplitBoxService::class)->ensureBatchHealth($fx2['splitBox']);

    $opened = SplitBoxBatch::where('split_box_id', $fx2['splitBox']->id)->where('batch_no', 2)->first();
    expect($opened?->opened_at)->not->toBeNull();
});

test('SplitRole pair requirement', function () {
    expect(SplitRole::Normal->pairRequirement())->toBe(0)
        ->and(SplitRole::Hot1->pairRequirement())->toBe(1)
        ->and(SplitRole::Hot2->pairRequirement())->toBe(2)
        ->and(SplitRole::Hot2->isHot())->toBeTrue();
});

test('kolom skema tambahan tersedia', function () {
    expect(Schema::hasColumns('orders', ['penalty_per_day', 'penalty_days']))->toBeTrue()
        ->and(Schema::hasColumn('carts', 'split_box_slot_id'))->toBeTrue()
        ->and(Schema::hasColumns('split_box_batches', ['opened_at', 'closed_at']))->toBeTrue();
});

test('penalty_per_day dan penalty_days dibekukan saat unlock', function () {
    [, $order] = placeTestOrder();
    $order->update(['status' => OrderStatus::Hangus, 'hangus_at' => now()->subDays(3)]);
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $order = app(LatePenaltyService::class)->unlockViaPenalty($order, $admin);
    $order = Order::find($order->id);

    expect((int) $order->penalty_per_day)->toBe(1000)
        ->and($order->penalty_days)->toBe(3)
        ->and((int) $order->penalty_amount)->toBe(3000);
});
