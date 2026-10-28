<?php

use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('slot tidak terkunci saat buat pesanan, terkunci saat upload bukti', function () {
    $fx = splitBoxFixture();
    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    // Buat pesanan: qty_taken masih 0
    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(0);

    $this->actingAs($fx['user'])->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(1)
        ->and((int) SplitBoxBatchSlot::where('slot_id', $fx['normalA']->id)->value('qty_taken'))->toBe(1);
});

test('lock atomik seluruh bundle â€” gagal satu gagal semua', function () {
    $fx = splitBoxFixture(); // stok 1 per slot

    // Orang lain mengunci Normal A lebih dulu
    [$other, $otherOrder] = placeSplitBoxOrder(
        User::factory()->create(),
        $fx['product'],
        [$fx['normalA']]
    );
    // pakai user lain dengan cart sendiri
    $this->actingAs($other)->post("/orders/{$otherOrder->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-other.jpg'),
    ]);

    // Order bundle Hot + Normal A â†’ Normal A habis â†’ seluruh bundle gagal
    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    $this->actingAs($fx['user'])->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // Hot tidak boleh terkunci (all-or-nothing)
    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(0);
});

test('alokasi fifo lintas kloter', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);

    // Kloter 2 dibuka
    $batch2 = SplitBoxBatch::create(['split_box_id' => $fx['splitBox']->id, 'batch_no' => 2, 'status' => 'open']);
    foreach ([$fx['hot'], $fx['normalA'], $fx['normalB']] as $slot) {
        SplitBoxBatchSlot::create(['batch_id' => $batch2->id, 'slot_id' => $slot->id, 'qty_total' => 5, 'qty_taken' => 0]);
    }

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    $this->actingAs($fx['user'])->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // Kloter 1 (batch_no kecil) yang dipakai
    $takenBatch1 = SplitBoxBatchSlot::where('batch_id', $fx['batch']->id)->where('slot_id', $fx['hot']->id)->value('qty_taken');
    $takenBatch2 = SplitBoxBatchSlot::where('batch_id', $batch2->id)->where('slot_id', $fx['hot']->id)->value('qty_taken');

    expect((int) $takenBatch1)->toBe(1)
        ->and((int) $takenBatch2)->toBe(0);
});

test('harga dan berat seluruh slot satu paket harus sama', function () {
    $fx = splitBoxFixture();
    $fx['product']->variants()->where('name', 'Hot')->update(['price_rmb' => 99]);

    expect(splitBoxService()->validateUniformPricing($fx['product']))->toBeFalse();
});
