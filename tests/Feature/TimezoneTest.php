<?php

use App\Models\User;
use App\Services\Order\OrderStatusService;

test('timezone aplikasi adalah asia jakarta', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(now()->timezone->getName())->toBe('Asia/Jakarta');
});

test('timestamp order ditampilkan dalam wib', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    // Carbon dari cast Eloquent memakai timezone aplikasi = WIB
    expect($order->fresh()->created_at->timezone->getName())->toBe('Asia/Jakarta');

    // Batas waktu upload dirender saat status Menunggu Pembayaran
    $html = $this->actingAs($user)->get('/orders/'.$order->no_order)->getContent();
    expect($html)->toContain($order->fresh()->payment_deadline_at->format('d/m/Y H:i'));

    // Waktu log status dirender di timeline
    app(OrderStatusService::class)->changeStatus($order, 'ditahan', 'user');
    $log = $order->statusLogs()->latest('id')->firstOrFail();
    $html = $this->actingAs($user)->get('/orders/'.$order->no_order)->getContent();

    expect($html)->toContain($log->created_at->format('d/m/Y H:i'));
});

test('invoice menampilkan tanggal dalam wib', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $html = view('frontend.order.invoice', ['order' => $order->load('items.snapshot')])->render();

    expect($html)->toContain($order->created_at->format('d/m/Y H:i'));
});

test('log status order memakai waktu wib', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);
    app(OrderStatusService::class)->changeStatus($order, 'ditahan', 'user');

    $log = $order->statusLogs()->latest('id')->firstOrFail();

    expect($log->created_at->timezone->getName())->toBe('Asia/Jakarta');
});
