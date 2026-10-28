<?php

use App\Models\SplitBoxBatch;
use App\Models\User;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\UploadedFile;

test('auto open kloter saat stok habis', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);

    expect(SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->count())->toBe(1);

    // Lock menghabiskan stok kloter 1 â†’ auto-open kloter 2
    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA'], $fx['normalB']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    $batches = SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->orderBy('batch_no')->get();
    expect($batches->count())->toBe(2)
        ->and($batches->last()->is_auto_opened)->toBeTrue()
        ->and($batches->last()->batch_no)->toBe(2);
});

test('auto open dibatasi max auto batches', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['splitBox']->update(['max_auto_batches' => 1]);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    expect(SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->count())->toBe(1);
});

test('cron splitbox check batches berjalan', function () {
    $fx = splitBoxFixture();
    $fx['batch']->update(['closes_at' => now()->subDay(), 'status' => 'open']);

    $this->artisan('splitbox:check-batches')->assertSuccessful();
});

test('tutup kloter dan refund semua mengembalikan status order', function () {
    $fx = splitBoxFixture(stockPerSlot: 2);
    [$userA, $orderA] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    // Upload â†’ lock slot (status Ditahan)
    $this->actingAs($userA)->post("/orders/{$orderA->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post("/office/split-box-batches/{$fx['batch']->id}/close-refund");

    expect($orderA->fresh()->status)->toBe('dana_dikembalikan')
        ->and($fx['batch']->fresh()->status)->toBe('closed');
});
