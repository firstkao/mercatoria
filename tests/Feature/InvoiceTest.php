<?php

use App\Models\User;

test('invoice pdf dapat diunduh pemilik order', function () {
    [$user, $order] = placeTestOrder();

    $response = $this->actingAs($user)->get("/orders/{$order->no_order}/invoice");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('pdf')
        ->and(strlen($response->getContent()))->toBeGreaterThan(0);
});

test('nomor order tampil dengan format mer di view invoice', function () {
    [, $order] = placeTestOrder();

    $html = view('frontend.order.invoice', ['order' => $order->load('items.snapshot')])->render();

    expect($order->no_order)->toMatch('/^MER-[A-Z]{4}-\d{4}$/')
        ->and($html)->toContain($order->no_order);
});

test('invoice membaca item dari snapshot bukan produk', function () {
    [, $order] = placeTestOrder();
    $order->load('items.snapshot');

    $order->items->first()->product->update(['name' => 'NAMA SUDAH DIUBAH']);

    $html = view('frontend.order.invoice', ['order' => $order])->render();

    expect($html)->toContain($order->items->first()->snapshot->product_name)
        ->and($html)->not->toContain('NAMA SUDAH DIUBAH');
});

test('invoice tidak memuat alamat pembeli', function () {
    $user = User::factory()->create(['alamat' => 'Jalan Rahasia Nomor 7']);
    [, $order] = placeTestOrder($user);

    $html = view('frontend.order.invoice', ['order' => $order->load('items.snapshot')])->render();

    expect($html)->not->toContain('Jalan Rahasia Nomor 7');
});

test('invoice menampilkan total diskon dan tagihan sesuai order', function () {
    [, $order] = placeTestOrder();

    $html = view('frontend.order.invoice', ['order' => $order->load('items.snapshot')])->render();

    // Produk final 255.000 × qty 1 → total 255.000, tagihan web 255.000
    expect($html)->toContain('255')
        ->and($html)->toContain($order->no_order);
});
