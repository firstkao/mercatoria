<?php

use App\Livewire\SplitBoxManager;
use App\Livewire\SplitBoxPicker;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
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

test('bulk edit harga/berat produk Split Box harus mencakup semua varian', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    $product = $fx['product'];
    $product->update(['is_split_box' => true]);
    $a = ProductVariant::factory()->create(['product_id' => $product->id]);
    $b = ProductVariant::factory()->create(['product_id' => $product->id]);
    $ids = $product->variants()->pluck('id')->all();

    $this->actingAs($admin)->postJson(route('office.products.variants.bulk-price', $product), ['ids' => [$a->id], 'price_rmb' => 10])
        ->assertStatus(422);
    $this->actingAs($admin)->postJson(route('office.products.variants.bulk-weight', $product), ['ids' => [$a->id], 'weight_gram' => 10])
        ->assertStatus(422);
    $this->actingAs($admin)->postJson(route('office.products.variants.bulk-price', $product), ['ids' => $ids, 'price_rmb' => 10])
        ->assertOk();

    expect($product->variants()->pluck('price_rmb')->map(fn ($p) => (float) $p)->unique()->all())->toBe([10.0]);
});

test('kloter tanpa order ditutup sebagai closed', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();

    app(SplitBoxService::class)->closeBatchAndRefund($fx['batch'], $admin);

    expect($fx['batch']->fresh()->status)->toBe(SplitBoxBatch::STATUS_CLOSED);
});

test('update produk Split Box menolak harga/berat varian yang tidak seragam', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    $product = $fx['product'];
    $product->variants()->first()->update(['price_rmb' => 99]);

    $this->actingAs($admin)->put(route('office.products.update', $product), [
        'game_id' => $product->game_id, 'store_id' => $product->store_id, 'name' => $product->name,
        'sku' => $product->sku, 'slug' => $product->slug, 'price_rmb' => 58, 'weight_gram' => 50,
        'type' => 'variant', 'availability' => 'tersedia', 'is_split_box' => 1,
    ])->assertSessionHasErrors('price_rmb');
});

test('duplikat produk Split Box menyalin SplitBox dan slot ke varian salinan', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('office.products.duplicate', $fx['product']))->assertRedirect();

    $copy = Product::where('id', '!=', $fx['product']->id)->where('is_split_box', true)->firstOrFail();
    $slots = $copy->splitBox->slots;

    expect($slots)->toHaveCount(3)
        ->and($slots->pluck('split_role')->sort()->values()->all())->toBe(['hot_1', 'normal', 'normal'])
        ->and($slots->every(fn ($s) => $s->variant->product_id === $copy->id))->toBeTrue()
        ->and($copy->splitBox->batches)->toHaveCount(0);
});

test('produk Split Box otomatis punya SplitBox dan slot per varian', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['price_rmb' => 10, 'weight_gram' => 10, 'is_split_box' => false]);

    $this->actingAs($admin)->put(route('office.products.update', $product), [
        'game_id' => $product->game_id, 'store_id' => $product->store_id, 'name' => $product->name,
        'sku' => $product->sku, 'slug' => $product->slug, 'price_rmb' => 10, 'weight_gram' => 10,
        'type' => 'variant', 'availability' => 'tersedia', 'is_split_box' => 1,
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->splitBox)->not->toBeNull();

    $this->actingAs($admin)->post(route('office.products.variants.store', $product), [
        'name' => 'Karakter A', 'price_rmb' => 10, 'weight_gram' => 10, 'availability' => 'tersedia',
    ])->assertSessionHasNoErrors();

    $slots = $product->fresh()->splitBox->slots;
    expect($slots)->toHaveCount(1)->and($slots->first()->split_role)->toBe('normal');
});

test('SplitBoxManager menyimpan role/qty slot dari form dan hanya untuk admin', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(SplitBoxManager::class, ['product' => $fx['product']])
        ->set("slotRoles.{$fx['normalA']->id}", 'hot_2')
        ->set("slotQtys.{$fx['normalA']->id}", 4)
        ->call('saveSlot', $fx['normalA']->id)
        ->assertSet('statusIsError', false);

    expect($fx['normalA']->fresh()->split_role)->toBe('hot_2')->and($fx['normalA']->fresh()->qty_per_box)->toBe(4);

    Livewire::actingAs($fx['user'])->test(SplitBoxManager::class, ['product' => $fx['product']])
        ->call('saveSlot', $fx['normalA']->id, 'normal', 1)
        ->assertForbidden();
});

test('kloter full tetap bisa ditutup oleh admin', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    $fx['batch']->update(['status' => SplitBoxBatch::STATUS_FULL]);

    $this->actingAs($admin)->post("/office/split-box-batches/{$fx['batch']->id}/close-refund")->assertRedirect();

    expect($fx['batch']->fresh()->status)->toBe(SplitBoxBatch::STATUS_CLOSED);
});

test('dua item dalam satu order pada slot yang sama tidak boleh melebihi stok kloter', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    $item = $order->items()->where('split_box_slot_id', $fx['normalA']->id)->first();
    $order->items()->create($item->replicate(['id', 'split_box_batch_slot_id'])->getAttributes());
    $fx['splitBox']->update(['auto_open_batch' => false]);

    expect(app(SplitBoxService::class)->lockSlotsForOrder($order->fresh()))->toBeFalse();
    expect($fx['batch']->batchSlots()->sum('qty_taken'))->toBe(0);
});

test('role slot tidak bisa diubah setelah ada penjualan, qty tetap boleh', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['normalA']]);
    OrderItem::query()->update(['split_box_slot_id' => $fx['normalA']->id]);

    Livewire::actingAs($admin)->test(SplitBoxManager::class, ['product' => $fx['product']])
        ->call('saveSlot', $fx['normalA']->id, 'hot_1', 3)
        ->assertSet('statusIsError', true)
        ->assertSet('statusMessage', 'Role slot tidak bisa diubah karena sudah ada penjualan.');

    expect($fx['normalA']->fresh()->split_role)->toBe('normal');

    Livewire::actingAs($admin)->test(SplitBoxManager::class, ['product' => $fx['product']])
        ->call('saveSlot', $fx['normalA']->id, 'normal', 3)
        ->assertSet('statusIsError', false);

    expect($fx['normalA']->fresh()->qty_per_box)->toBe(3);
});

test('max auto batches tidak menghitung kloter closed, tetap membuka kloter baru', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['splitBox']->update(['max_auto_batches' => 2]);
    foreach ([2, 3] as $no) {
        SplitBoxBatch::create(['split_box_id' => $fx['splitBox']->id, 'batch_no' => $no, 'status' => SplitBoxBatch::STATUS_CLOSED]);
    }

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA'], $fx['normalB']]);
    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeTrue();

    $batches = SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->get();
    expect($batches)->toHaveCount(4)
        ->and($batches->where('status', SplitBoxBatch::STATUS_OPEN)->where('is_auto_opened', true))->toHaveCount(1);
});

test('max auto batches: dua kloter open/full mencapai batas, tidak membuka kloter baru', function () {
    $fx = splitBoxFixture(stockPerSlot: 1);
    $fx['splitBox']->update(['max_auto_batches' => 2]);
    SplitBoxBatch::create(['split_box_id' => $fx['splitBox']->id, 'batch_no' => 2, 'status' => SplitBoxBatch::STATUS_FULL]);

    [, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA'], $fx['normalB']]);
    expect(app(SplitBoxService::class)->lockSlotsForOrder($order))->toBeTrue();

    expect(SplitBoxBatch::where('split_box_id', $fx['splitBox']->id)->count())->toBe(2);
});
