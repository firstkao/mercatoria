<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\Order;
use App\Models\User;

test('checkout membuat order, item, dan snapshot dalam satu transaksi', function () {
    $user = User::factory()->create();
    $product = productFinal255();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 2]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
    ]))->assertRedirect();

    $order = Order::where('user_id', $user->id)->firstOrFail();

    expect($order->status)->toBe('menunggu_pembayaran')
        ->and($order->no_order)->toMatch('/^MER-[A-Z]{4}-\d{4}$/')
        ->and((float) $order->subtotal)->toEqual(510000.0)
        ->and((float) $order->total)->toEqual(510000.0)
        ->and($order->customer_note)->toBe('bubble wrap extra')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->qty)->toBe(2);

    $snapshot = $order->items->first()->snapshot;
    expect($snapshot->product_name)->toBe($product->name)
        ->and($snapshot->product_sku)->toBe($product->sku)
        ->and($snapshot->weight_gram)->toBe(500)
        ->and($snapshot->store_name)->not->toBeNull()
        ->and((float) $snapshot->final_price)->toEqual(255000.0)
        ->and((float) $snapshot->subtotal)->toEqual(510000.0)
        ->and((float) $snapshot->ongkir_total_idr)->toEqual(54680.0);
});

test('harga dan ongkir freeze di snapshot walau produk berubah', function () {
    $user = User::factory()->create();
    $product = productFinal255();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['cart_item_ids' => [$item->id]]));

    // Ubah semua sumber harga SETELAH order dibuat
    $product->store->update(['name' => 'Store Diubah', 'ongkir_rmb' => 99]);
    $product->update(['price_rmb' => 999, 'weight_gram' => 5000, 'name' => 'Nama Diubah']);

    $snapshot = Order::where('user_id', $user->id)->firstOrFail()->items->first()->snapshot;

    expect($snapshot->product_name)->not->toBe('Nama Diubah')
        ->and((float) $snapshot->final_price)->toEqual(255000.0)
        ->and($snapshot->weight_gram)->toBe(500)
        ->and($snapshot->store_name)->not->toBe('Store Diubah')
        ->and((float) $snapshot->ongkir_lokal_cn_idr)->toEqual(12180.0)
        ->and((float) $snapshot->ongkir_cn_btm_idr)->toEqual(12500.0)
        ->and((float) $snapshot->ongkir_btm_jkt_idr)->toEqual(30000.0);
});

test('item cart yang di-checkout dihapus dari keranjang', function () {
    $user = User::factory()->create();
    $checkedOut = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $leftBehind = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal85()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$checkedOut->id],
    ]))->assertRedirect();

    expect(Cart::where('id', $checkedOut->id)->exists())->toBeFalse()
        ->and(Cart::where('id', $leftBehind->id)->exists())->toBeTrue();
});

test('order sukses menampilkan halaman konfirmasi', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['cart_item_ids' => [$item->id]]))
        ->assertRedirect();

    $order = Order::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get("/orders/{$order->no_order}")
        ->assertOk()
        ->assertSee($order->no_order)
        ->assertSee('Menunggu Pembayaran');
});

test('ringkasan checkout menghitung ulang dari server', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $response = $this->actingAs($user)->postJson('/checkout/summary', checkoutPayload([
        'cart_item_ids' => [$item->id],
    ]));

    $response->assertOk()
        ->assertJsonPath('subtotal', 255000)
        ->assertJsonPath('total_barang', 255000);
});

test('qty besar dihitung per item lalu dikalikan', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 3]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['cart_item_ids' => [$item->id]]));

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect((float) $order->subtotal)->toEqual(765000.0);
});

test('dp 50 persen tersimpan di order', function () {
    $user = User::factory()->create();
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'scheme' => 'dp',
        'platform' => 'shopee',
    ]));

    $order = Order::where('user_id', $user->id)->firstOrFail();

    expect($order->scheme)->toBe('dp')
        ->and((float) $order->tagihan_web)->toEqual(127500.0)
        ->and((float) $order->estimasi_co_marketplace)->toEqual(159375.0);
});

test('checkout memakai koin dalam satu transaksi dengan order', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 12750]);
    $item = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$item->id],
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]));

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect((float) $order->discount_amount)->toEqual(12750.0)
        ->and((float) $order->total)->toEqual(242250.0)
        ->and($order->discount_type)->toBe('coin');
});
