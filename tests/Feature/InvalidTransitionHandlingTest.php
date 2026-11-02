<?php

use App\Models\Order;
use App\Models\User;

test('ubah status ilegal oleh admin dibalas error validasi, bukan 500', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->from('/office')->patch(route('office.orders.status', $order->no_order), ['status' => 'selesai'])
        ->assertRedirect('/office')
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('buka kunci denda pada order yang tidak hangus tidak mengubah kolom denda', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->from('/office')->post(route('office.orders.unlock-penalty', $order->no_order), ['penalty_amount' => 5000])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->penalty_amount)->toBeNull()->and($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('bulk status all-or-nothing dan menolak status tak dikenal', function () {
    $admin = User::factory()->admin()->create();
    [, $a] = placeTestOrder();
    [, $b] = placeTestOrder();

    $this->actingAs($admin)->post(route('office.orders.bulk-status'), ['status' => 'ngawur', 'no_orders' => [$a->no_order]])
        ->assertSessionHasErrors('status');

    $this->actingAs($admin)->post(route('office.orders.bulk-status'), ['status' => 'selesai', 'no_orders' => [$a->no_order, $b->no_order]])
        ->assertSessionHasErrors('status');

    expect(Order::whereIn('id', [$a->id, $b->id])->pluck('status')->unique()->all())->toBe(['menunggu_pembayaran']);
});

test('settings menolak pembulatan harga 0 dan per halaman katalog 0', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('office.settings.update'), ['settings' => ['price_rounding' => '0', 'catalog_per_page' => '0', 'late_penalty_pay_hours' => '0']])
        ->assertSessionHasErrors(['settings.price_rounding', 'settings.catalog_per_page', 'settings.late_penalty_pay_hours']);
});
