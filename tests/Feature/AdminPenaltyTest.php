<?php

use App\Models\AdminLog;
use App\Models\Customer;
use App\Models\User;

test('admin membuka kunci via denda tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/unlock-penalty", [
        'penalty_amount' => 2000,
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe('menunggu_denda')
        ->and(AdminLog::where('model', 'Order')->where('model_id', $order->id)->exists())->toBeTrue();
});

test('admin menetapkan hangus permanen tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()]);

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/hangus-permanent");

    expect($order->fresh()->status)->toBe('hangus_permanen')
        ->and(AdminLog::where('description', 'like', '%Hangus Permanen%')->exists())->toBeTrue();
});

test('laporan menampilkan kartu denda diterima', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'hangus', 'hangus_at' => now()->subDays(2)]);

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/unlock-penalty", [
        'penalty_amount' => 3000,
    ]);
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/hangus-permanent");

    $this->actingAs($admin)->get('/office/reports')
        ->assertOk()
        ->assertSee('Denda Diterima');
});

test('dashboard user menampilkan progress bar tier', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'tier' => 'silver', 'total_belanja' => 500000]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Progress Tier')
        ->assertSee('MVP Silver');
});
