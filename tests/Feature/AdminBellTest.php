<?php

use App\Models\AdminNotification;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Payment\RefundService;
use Illuminate\Http\UploadedFile;

test('order baru membuat notif admin', function () {
    [, $order] = placeTestOrder();

    $notif = AdminNotification::where('type', 'order_baru')->sole();

    expect($notif->title)->toContain($order->no_order)
        ->and($notif->target_url)->toBe('/office/orders/'.$order->no_order)
        ->and($notif->is_read)->toBeFalse();
});

test('bukti bayar diupload membuat notif admin', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    $notif = AdminNotification::where('type', 'bukti_bayar')->sole();

    expect($notif->title)->toContain($order->no_order)
        ->and($notif->target_url)->toBe('/office/orders/'.$order->no_order);
});

test('refund request membuat notif admin', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();
    $order->update(['status' => 'ditahan']);

    app(RefundService::class)->requestFull($order, 'Kosong', $admin);

    $notif = AdminNotification::where('type', 'refund_request')->sole();

    expect($notif->title)->toContain($order->no_order)
        ->and($notif->target_url)->toBe('/office/orders/'.$order->no_order);
});

test('order lewat batas 7 hari membuat notif admin', function () {
    [, $order] = placeTestOrder();
    $order->update([
        'status' => 'menunggu_pelunasan_pengambilan',
        'pickup_deadline_at' => now()->subHour(),
    ]);

    $this->artisan('orders:hangus-expired')->assertSuccessful();

    $notif = AdminNotification::where('type', 'order_nearing_expiry')->sole();

    expect($notif->title)->toContain($order->no_order)
        ->and($notif->target_url)->toBe('/office/orders/'.$order->no_order);
});

test('tandai semua dibaca mengosongkan unread count', function () {
    $admin = User::factory()->admin()->create();
    app(AdminNotificationService::class)->notify('order_baru', 'Satu', '/office');
    app(AdminNotificationService::class)->notify('order_baru', 'Dua', '/office');

    $this->actingAs($admin)->post('/office/notifications/read-all')->assertRedirect();

    expect(AdminNotification::where('is_read', false)->count())->toBe(0);
});

test('purge 90 hari menghapus notifikasi admin lama', function () {
    $service = app(AdminNotificationService::class);
    $service->notify('order_baru', 'Baru', '/office');
    $old = $service->notify('order_baru', 'Tua', '/office');
    AdminNotification::where('id', $old->id)->update(['created_at' => now()->subDays(91)]);

    $this->artisan('notification:purge')->assertSuccessful();

    expect(AdminNotification::count())->toBe(1)
        ->and(AdminNotification::first()->title)->toBe('Baru');
});

test('topbar data admin membawa 10 terbaru dan unread count', function () {
    foreach (range(1, 12) as $i) {
        app(AdminNotificationService::class)->notify('order_baru', 'Notif '.$i, '/office');
    }

    $data = app(AdminNotificationService::class)->topbarData();

    expect($data['notifications']->count())->toBe(10)
        ->and($data['notifications']->first()->title)->toBe('Notif 12')
        ->and($data['unread'])->toBe(12);
});

test('non-admin ditolak 403 read-all notif admin', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post('/office/notifications/read-all')->assertForbidden();
});
