<?php

use App\Models\User;

test('admin dapat mengunduh invoice pdf order', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $response = $this->actingAs($admin)->get("/office/orders/{$order->no_order}/invoice")->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain($order->no_order);
});

test('non-admin ditolak unduh invoice admin', function () {
    $customer = User::factory()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($customer)->get("/office/orders/{$order->no_order}/invoice")->assertForbidden();
});
