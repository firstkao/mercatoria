<?php

use App\Models\User;
use App\Services\Payment\RefundService;
use Illuminate\Support\Facades\View;

test('daftar refund tampil dengan filter status', function () {
    // Stub view sementara (di luar resources/views) agar controller teruji tanpa menyentuh UI.
    $stubDir = sys_get_temp_dir().'/mercatoria-test-views';
    if (! is_dir($stubDir.'/admin/refunds')) {
        mkdir($stubDir.'/admin/refunds', 0777, true);
    }
    file_put_contents(
        $stubDir.'/admin/refunds/index.blade.php',
        '@foreach($refunds as $r){{ $r->order->no_order }}|{{ $r->status }};@endforeach'
    );
    View::addLocation($stubDir);

    $admin = User::factory()->admin()->create();
    [, $orderA] = placeTestOrder();
    [, $orderB] = placeTestOrder();
    $orderA->update(['status' => 'ditahan']);
    $orderB->update(['status' => 'ditahan']);

    app(RefundService::class)->requestFull($orderA, 'Kosong', $admin);
    $b = app(RefundService::class)->requestFull($orderB, 'Kosong', $admin);
    app(RefundService::class)->complete($b, $admin);

    $html = $this->actingAs($admin)->get('/office/refunds')->assertOk()->getContent();
    expect($html)->toContain($orderA->no_order)->toContain($orderB->no_order);

    // Kolom bell admin di layout bisa memuat judul notif berisi no_order —
    // bersihkan agar asersi filter hanya menilai tabel daftar refund.
    \App\Models\AdminNotification::query()->delete();

    $filtered = $this->actingAs($admin)->get('/office/refunds?status=completed')->assertOk()->getContent();
    expect($filtered)->toContain($orderB->no_order)
        ->and($filtered)->not->toContain($orderA->no_order);
});

test('non-admin ditolak membuka daftar refund', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get('/office/refunds')->assertForbidden();
});
