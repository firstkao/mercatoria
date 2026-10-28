<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin mengubah status order single', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)
        ->patch("/office/orders/{$order->no_order}/status", ['status' => 'ditahan'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('ditahan');
});

test('admin mengubah status banyak order sekaligus', function () {
    $admin = User::factory()->admin()->create();
    [, $orderA] = placeTestOrder();
    [, $orderB] = placeTestOrder();

    $this->actingAs($admin)->post('/office/orders/bulk-status', [
        'status' => 'ditahan',
        'no_orders' => [$orderA->no_order, $orderB->no_order],
    ])->assertRedirect();

    expect($orderA->fresh()->status)->toBe('ditahan')
        ->and($orderB->fresh()->status)->toBe('ditahan');
});

test('admin verifikasi dan menolak bukti bayar', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/verify");
    expect($order->fresh()->status)->toBe('pembayaran_diterima');

    [$userB, $orderB] = placeTestOrder();
    $this->actingAs($userB)->post("/orders/{$orderB->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti2.jpg'),
    ]);
    $paymentB = Payment::where('order_id', $orderB->id)->firstOrFail();

    $this->actingAs($admin)->post("/office/orders/{$orderB->no_order}/payments/{$paymentB->id}/reject", ['reason' => 'blur']);
    expect($orderB->fresh()->status)->toBe('menunggu_pembayaran')
        ->and($paymentB->fresh()->status)->toBe('rejected');
});

test('admin dapat melihat daftar pembayaran yang menunggu verifikasi', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $this->actingAs($admin)->get('/office/payments')
        ->assertOk()
        ->assertSee($order->no_order);
});

test('admin mencatat pelunasan settlement', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/settlements", [
        'amount' => 100000,
    ])->assertRedirect();

    $settlement = Payment::where('order_id', $order->id)->where('type', 'settlement')->firstOrFail();
    expect($settlement->status)->toBe('verified')
        ->and((float) $settlement->amount)->toEqual(100000.0);
});
