<?php

use App\Models\Cart;
use App\Models\Payment;
use App\Models\User;
use App\Services\Cart\CheckoutService;

test('laporan menampilkan kartu dasar dengan angka benar', function () {
    $admin = User::factory()->admin()->create();

    // Order selesai 255.000 (penjualan)
    [, $selesai] = placeTestOrder();
    advanceToSelesai($selesai);

    // Order DP dengan pembayaran dp 127.500 terverifikasi + settlement 100.000
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id, 'qty' => 1]);
    $dpOrder = app(CheckoutService::class)->placeOrder($user, [$cart->id], [
        'platform' => 'toco', 'scheme' => 'dp', 'payment_method' => 'bca', 'discount_type' => 'none', 'customer_note' => null,
    ]);
    Payment::factory()->create([
        'order_id' => $dpOrder->id,
        'type' => 'dp',
        'amount' => 127500,
        'status' => 'verified',
    ]);
    Payment::factory()->create([
        'order_id' => $dpOrder->id,
        'type' => 'settlement',
        'amount' => 100000,
        'status' => 'verified',
    ]);

    // Order hangus dengan pembayaran full 200.000 terverifikasi
    [, $hangus] = placeTestOrder();
    Payment::factory()->create([
        'order_id' => $hangus->id,
        'type' => 'full',
        'amount' => 200000,
        'status' => 'verified',
    ]);
    $hangus->update(['status' => 'hangus']);

    $this->actingAs($admin)->get('/office/reports')
        ->assertOk()
        ->assertSee('Total DP Diterima')
        ->assertSee('Total Pelunasan')
        ->assertSee('Total Penjualan')
        ->assertSee('127.500')
        ->assertSee('100.000')
        ->assertSee('255.000');
});

test('kartu denda diterima tampil di laporan', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/office/reports')
        ->assertOk()
        ->assertSee('Denda Diterima');
});
