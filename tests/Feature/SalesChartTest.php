<?php

use App\Models\User;

test('endpoint grafik penjualan mengembalikan array 30 elemen', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder(); // hari ini

    $json = $this->actingAs($admin)->getJson('/office/reports/sales-chart')->assertOk()->json();

    expect(count($json))->toBe(30);

    $terakhir = collect($json)->last();
    expect($terakhir['date'])->toBe(now()->toDateString())
        ->and((float) $terakhir['revenue'])->toBe((float) $order->total);

    // Hari tanpa order tetap nol
    $pertama = collect($json)->first();
    expect((float) $pertama['revenue'])->toBe(0.0);
});

test('grafik penjualan tidak menghitung order pembayaran gagal', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'pembayaran_gagal']);

    $json = $this->actingAs($admin)->getJson('/office/reports/sales-chart')->assertOk()->json();

    expect((float) collect($json)->last()['revenue'])->toBe(0.0);
});

test('non-admin ditolak 403 grafik penjualan', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->getJson('/office/reports/sales-chart')->assertForbidden();
});
