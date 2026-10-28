<?php

use App\Models\Crush;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('user terverifikasi mendapat row crush', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($url)->assertRedirect('/dashboard');

    expect($user->fresh()->email_verified_at)->not->toBeNull()
        ->and(Crush::where('user_id', $user->id)->exists())->toBeTrue();
});

test('link verifikasi dengan hash salah ditolak', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => 'hash-salah']
    );

    $this->get($url)->assertForbidden();

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('link verifikasi hanya berlaku 24 jam sejak pendaftaran', function () {
    $user = User::factory()->unverified()->create([
        'created_at' => now()->subHours(25),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->from('/email/verify')->get($url)
        ->assertRedirect('/email/verify');

    expect($user->fresh()->email_verified_at)->toBeNull()
        ->and(session('error'))->toContain('kedaluwarsa');
});

test('user yang belum verifikasi dapat meminta ulang email verifikasi', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('/email/resend')
        ->assertRedirect('/email/verify');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('halaman notice verifikasi tampil untuk user belum verifikasi', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertOk();
});
