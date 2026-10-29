<?php

use App\Models\Order;
use App\Models\User;

test('order mendapat nomor invoice INV-MER', function () {
    [, $order] = placeTestOrder();

    expect($order->invoice_number)->toMatch('/^INV-MER-\d{4}$/');
});

test('nomor invoice sinkron dengan sequence nomor order', function () {
    [, $order] = placeTestOrder();

    expect(substr($order->invoice_number, -4))->toBe(substr($order->no_order, -4));
});

test('nomor invoice unik antar order', function () {
    [, $first] = placeTestOrder();
    [, $second] = placeTestOrder();

    expect($first->invoice_number)->not->toBe($second->invoice_number);
});

test('inject order lama juga mendapat nomor invoice', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => $customer->id,
        'status' => 'selesai',
        'items' => [['product_id' => productFinal255()->id, 'variant_id' => null, 'qty' => 1, 'price' => 10000]],
    ])->assertRedirect();

    $order = Order::where('user_id', $customer->id)->firstOrFail();

    expect($order->invoice_number)->toMatch('/^INV-MER-\d{4}$/');
});
