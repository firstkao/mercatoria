<?php

use App\Mail\OrderCreatedMail;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

test('order baru mengirim email konfirmasi', function () {
    Mail::fake();
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    Mail::assertSent(OrderCreatedMail::class);
});

test('pause customer emails menahan email order', function () {
    Mail::fake();
    Setting::create(['key' => 'pause_customer_emails', 'value' => '1', 'type' => 'bool', 'group' => 'email', 'description' => 'test']);

    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    Mail::assertNotSent(OrderCreatedMail::class);
});

test('email verifikasi tetap terkirim walau pause aktif', function () {
    Notification::fake();
    Setting::create(['key' => 'pause_customer_emails', 'value' => '1', 'type' => 'bool', 'group' => 'email', 'description' => 'test']);

    $this->post('/register', registerPayload(['email' => 'tetap@example.com']))
        ->assertRedirect('/email/verify');

    $user = User::where('email', 'tetap@example.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});
