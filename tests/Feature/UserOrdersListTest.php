<?php

use App\Models\User;

test('user melihat daftar riwayat order', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $this->actingAs($user)->get('/orders')
        ->assertOk()
        ->assertSee($order->no_order);
});

test('user lain tidak melihat order orang lain di daftar', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $other = User::factory()->create();

    $this->actingAs($other)->get('/orders')
        ->assertOk()
        ->assertDontSee($order->no_order);
});

test('daftar order menampilkan status dan link detail', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $this->actingAs($user)->get('/orders')
        ->assertOk()
        ->assertSee('Menunggu Pembayaran')
        ->assertSee(route('orders.show', $order->no_order), false);
});
