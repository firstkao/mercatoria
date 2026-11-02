<?php

use App\Http\Controllers\TwoFactorController;
use App\Models\User;
use App\Notifications\AdminOtpNotification;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

test('admin harus verifikasi otp setelah password benar', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123'])
        ->assertRedirect('/2fa/verify');

    $this->assertGuest();
    Notification::assertSentTo($admin, AdminOtpNotification::class);
});

test('otp benar membuka akses office', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $code = cache()->get('admin_otp:'.$admin->id);

    $this->post('/2fa/verify', ['code' => $code])
        ->assertRedirect('/office');

    $this->assertAuthenticatedAs($admin);
});

test('otp salah ditolak', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/2fa/verify', ['code' => '000000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('otp expired setelah 5 menit tidak berlaku', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $code = cache()->get('admin_otp:'.$admin->id);
    cache()->forget('admin_otp:'.$admin->id);

    $this->post('/2fa/verify', ['code' => $code])
        ->assertSessionHasErrors('code');
});

test('customer tidak memerlukan otp', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password123'])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
    Notification::assertNotSentTo($user, AdminOtpNotification::class);
});

test('halaman 2fa mengirim otpExpiresAt', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $response = $this->get('/2fa/verify')->assertOk();

    $expiresAt = Carbon::parse($response->viewData('otpExpiresAt'));
    $sisaDetik = now()->diffInSeconds($expiresAt, false);

    expect($sisaDetik)->toBeGreaterThan(290)->toBeLessThanOrEqual(300);
});

test('countdown otp memakai otpExpiresAt di halaman', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $this->post('/login', ['email' => 'admin@mercatoria.id', 'password' => 'password123']);

    $expiresAt = cache()->get('admin_otp_expires_at:'.$admin->id);
    $html = $this->get('/2fa/verify')->assertOk()->getContent();

    expect($html)->toContain((string) Carbon::parse($expiresAt)->getTimestampMs());
});

test('OTP dihanguskan setelah 5 tebakan salah', function () {
    $admin = User::factory()->admin()->create();
    Notification::fake();
    $code = TwoFactorController::issue($admin);
    $this->withoutMiddleware(ThrottleRequests::class);
    $this->withSession(['admin_2fa_id' => $admin->id]);

    foreach (range(1, 5) as $i) {
        $wrong = $code === '000000' ? '111111' : '000000';
        $this->post('/2fa/verify', ['code' => $wrong])->assertSessionHasErrors('code');
    }

    $this->post('/2fa/verify', ['code' => $code])->assertSessionHasErrors('code');
    $this->assertGuest();
});
