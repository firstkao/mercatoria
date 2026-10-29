<?php

use App\Models\User;
use App\Notifications\PaymentProofAdminNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;

test('upload bukti bayar mengirim email ke admin utama dan cs@', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    Notification::assertSentTo($admin, PaymentProofAdminNotification::class);
    Notification::assertSentOnDemand(PaymentProofAdminNotification::class, function ($notification, $channels, $notifiable) {
        return ($notifiable->routes['mail'] ?? null) === 'cs@mercatoria.id';
    });
});

test('email notif admin memuat detail bukti bayar', function () {
    Notification::fake();
    User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    // Konten wajib (PRD 17.11): no order, nama, email, total, tipe, metode, link bukti
    $admin = User::where('role', 'admin')->first();
    $sent = Notification::sent($admin, PaymentProofAdminNotification::class)->first();
    $rendered = (string) $sent->toMail($admin)->render();

    expect($rendered)->toContain($order->no_order)
        ->toContain($user->email)
        ->toContain(route('office.orders.show', $order));
});

test('customer non-admin tidak menerima email notif bukti bayar', function () {
    Notification::fake();
    $other = User::factory()->create();
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    Notification::assertNotSentTo($other, PaymentProofAdminNotification::class);
});
