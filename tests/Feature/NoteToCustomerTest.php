<?php

use App\Models\AdminLog;
use App\Models\User;
use App\Models\UserNotification;

test('admin kirim note to customer tersimpan di order', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/note-to-customer", [
        'note_to_customer' => 'Barangmu sudah dicek, packing rapi ya.',
    ])->assertRedirect();

    $order->refresh();

    expect($order->note_to_customer)->toBe('Barangmu sudah dicek, packing rapi ya.')
        ->and($order->note_to_customer_updated_at)->not->toBeNull();
});

test('note to customer mendorong bell order_note ke user', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/note-to-customer", [
        'note_to_customer' => 'Halo dari admin.',
    ]);

    expect(UserNotification::where('user_id', $user->id)
        ->where('type', 'order_note')
        ->where('target_url', '/orders/'.$order->no_order)
        ->exists())->toBeTrue();
});

test('note to customer tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/note-to-customer", [
        'note_to_customer' => 'Catatan.',
    ]);

    expect(AdminLog::where('model', 'Order')
        ->where('action', 'note_to_customer')
        ->where('model_id', $order->id)
        ->exists())->toBeTrue();
});

test('note to customer maksimal 1000 karakter', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/note-to-customer", [
        'note_to_customer' => str_repeat('a', 1001),
    ])->assertSessionHasErrors('note_to_customer');

    expect($order->fresh()->note_to_customer)->toBeNull();
});

test('non-admin ditolak 403 kirim note to customer', function () {
    $customer = User::factory()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($customer)->post("/office/orders/{$order->no_order}/note-to-customer", [
        'note_to_customer' => 'Coba.',
    ])->assertForbidden();

    expect($order->fresh()->note_to_customer)->toBeNull();
});
