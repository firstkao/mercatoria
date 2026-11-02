<?php

use App\Livewire\SplitBoxPicker;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Cart\CheckoutService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('cron splitbox:check-batches harian 08:30 WIB', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command, 'splitbox:check-batches'));

    expect($event->expression)->toBe('30 8 * * *')->and($event->timezone)->toBe('Asia/Jakarta');
});

test('kloter menjadi full saat seluruh slot terisi dan terbuka lagi saat dilepas', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['splitBox']->update(['auto_open_batch' => false]);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    $fx['batch']->batchSlots()->where('slot_id', $fx['normalB']->id)->update(['qty_taken' => 1]);

    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeTrue()
        ->and($fx['batch']->fresh()->status)->toBe(SplitBoxBatch::STATUS_FULL);

    app(SplitBoxService::class)->releaseSlotsForOrder($order);

    expect($fx['batch']->fresh()->status)->toBe(SplitBoxBatch::STATUS_OPEN);
});

test('deadlock pair membuka kloter baru otomatis', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    // Normal habis, Hot masih tersisa → deadlock (stok Normal 0 < kebutuhan pair 1)
    SplitBoxBatchSlot::whereIn('slot_id', [$fx['normalA']->id, $fx['normalB']->id])->update(['qty_taken' => 1]);

    app(SplitBoxService::class)->ensureBatchHealth($fx['splitBox']);

    expect(SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->where('batch_no', 2)->exists())->toBeTrue();
});

test('upload bukti memvalidasi ulang pair', function () {
    $fx = splitBoxFixture();
    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    OrderItem::where('order_id', $order->id)->where('split_box_slot_id', $fx['normalA']->id)->delete();

    expect(fn () => app(SplitBoxService::class)->assertOrderPairsValid($order->fresh()))
        ->toThrow(ValidationException::class);
});

test('slot terkunci membersihkan cart user lain dan mengirim bell + toast', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $other = User::factory()->create();
    $bundle = (string) Str::uuid();
    Cart::create(['user_id' => $other->id, 'product_id' => $fx['product']->id, 'variant_id' => $fx['normalA']->variant_id, 'qty' => 1, 'bundle_group_id' => $bundle]);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    expect(Cart::where('bundle_group_id', $bundle)->exists())->toBeFalse()
        ->and(UserNotification::where('user_id', $other->id)->where('type', 'split_cart_purged')->exists())->toBeTrue();

    $this->actingAs($other)->get('/cart')->assertSee('slot karakter di keranjangmu telah terjual');
});

test('order slot_sold mengirim bell dan menutup kloter refund mengirim bell', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $loser = User::factory()->create();
    [, $loserOrder] = placeSplitBoxOrder($loser, $fx['product'], [$fx['normalA']]);
    [, $winnerOrder] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['normalA']]);

    app(SplitBoxService::class)->lockSlotsForOrder($winnerOrder);

    expect($loserOrder->fresh()->cancel_reason)->toBe(Order::CANCEL_SLOT_SOLD)
        ->and(UserNotification::where('user_id', $loser->id)->where('type', 'split_slot_sold')->exists())->toBeTrue();

    $admin = User::factory()->admin()->create();
    app(SplitBoxService::class)->closeBatchAndRefund($fx['batch'], $admin);

    expect(UserNotification::where('user_id', $fx['user']->id)->where('type', 'split_batch_cancelled')->exists())->toBeTrue();
});

test('qty item bundle di keranjang tidak bisa diubah satu per satu', function () {
    $fx = splitBoxFixture();
    $bundle = (string) Str::uuid();
    $cart = Cart::create(['user_id' => $fx['user']->id, 'product_id' => $fx['product']->id, 'variant_id' => $fx['normalA']->variant_id, 'qty' => 1, 'bundle_group_id' => $bundle]);

    $this->actingAs($fx['user'])->patch("/cart/{$cart->id}", ['qty' => 5])->assertSessionHasErrors('qty');

    expect($cart->fresh()->qty)->toBe(1);
});

test('checkout parsial bundle ditolak', function () {
    $fx = splitBoxFixture();
    $bundle = (string) Str::uuid();
    $a = Cart::create(['user_id' => $fx['user']->id, 'product_id' => $fx['product']->id, 'variant_id' => $fx['normalA']->variant_id, 'qty' => 1, 'bundle_group_id' => $bundle]);
    Cart::create(['user_id' => $fx['user']->id, 'product_id' => $fx['product']->id, 'variant_id' => $fx['normalB']->variant_id, 'qty' => 1, 'bundle_group_id' => $bundle]);

    expect(fn () => app(CheckoutService::class)->placeOrder($fx['user'], [$a->id], [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]))->toThrow(ValidationException::class);
});

test('picker menonaktifkan tombol sampai pair terpenuhi dan menampilkan status pair', function () {
    $fx = splitBoxFixture(stockPerSlot: 2);

    $this->actingAs($fx['user']);

    Livewire::test(SplitBoxPicker::class, ['product' => $fx['product']])
        ->call('increment', $fx['hot']->id)
        ->assertSee('Kamu memilih 1 Hot Item. Pilih 1 karakter Normal lagi untuk lanjut.')
        ->call('increment', $fx['normalA']->id)
        ->assertSee('Syarat pair terpenuhi.');
});

test('variant split box harus harga dan berat seragam', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    $variant = $fx['normalA']->variant;

    $this->actingAs($admin)->put("/office/products/{$fx['product']->id}/variants/{$variant->id}", [
        'name' => $variant->name, 'sku' => $variant->sku, 'price_rmb' => 99, 'weight_gram' => 50, 'availability' => $variant->availability ?? 'ready',
    ])->assertSessionHasErrors('price_rmb');
});
