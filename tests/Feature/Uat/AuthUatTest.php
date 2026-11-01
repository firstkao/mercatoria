<?php

use App\Models\User;
use App\Notifications\AdminOtpNotification;
use Illuminate\Support\Facades\Notification;

test('UAT register: user baru terdaftar, login otomatis, diarahkan ke verifikasi email', function () {
    $this->get('/register')->assertOk()->assertSee('name="email"', false);

    $this->post('/register', registerPayload(['email' => 'uat-baru@example.com']))
        ->assertRedirect('/email/verify');

    $this->assertAuthenticated();
    $this->get('/email/verify')->assertOk();
});

test('UAT register: field wajib kosong menampilkan error dan tidak membuat user', function () {
    $this->from('/register')->post('/register', registerPayload(['email' => '', 'name' => '', 'password' => '']))
        ->assertRedirect('/register')
        ->assertSessionHasErrors(['email', 'name', 'password']);

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('UAT register: email duplikat ditolak', function () {
    User::factory()->create(['email' => 'dobel@example.com']);

    $this->post('/register', registerPayload(['email' => 'dobel@example.com']))
        ->assertSessionHasErrors('email');

    expect(User::where('email', 'dobel@example.com')->count())->toBe(1);
});

test('UAT login: customer valid masuk ke /dashboard dan melihat halamannya', function () {
    User::factory()->create(['email' => 'cust@example.com', 'password' => 'password123']);

    $this->post('/login', ['email' => 'cust@example.com', 'password' => 'password123'])
        ->assertRedirect('/dashboard');

    $this->get('/dashboard')->assertOk();
});

test('UAT login: kredensial salah menampilkan error di halaman login', function () {
    User::factory()->create(['email' => 'cust@example.com', 'password' => 'password123']);

    $this->from('/login')->post('/login', ['email' => 'cust@example.com', 'password' => 'salah'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->followingRedirects()->from('/login')
        ->post('/login', ['email' => 'cust@example.com', 'password' => 'salah'])
        ->assertOk();
});

test('UAT login: field kosong ditolak validasi', function () {
    $this->post('/login', ['email' => '', 'password' => ''])
        ->assertSessionHasErrors(['email', 'password']);
});

test('UAT login admin: wajib OTP 2FA lalu masuk ke /office', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['email' => 'adm@example.com', 'password' => 'password123']);

    $this->post('/login', ['email' => 'adm@example.com', 'password' => 'password123'])
        ->assertRedirect('/2fa/verify');
    $this->assertGuest();
    Notification::assertSentTo($admin, AdminOtpNotification::class);

    $this->post('/2fa/verify', ['code' => cache()->get('admin_otp:'.$admin->id)])
        ->assertRedirect('/office');

    $this->assertAuthenticatedAs($admin);
    $this->get('/office')->assertOk();
});

test('UAT logout: sesi dihancurkan dan halaman terproteksi kembali ke login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
    $this->get('/dashboard')->assertRedirect('/login');
});

test('UAT guest: semua route customer terproteksi diarahkan ke /login', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect('/login');
})->with([
    ['GET', '/dashboard'],
    ['GET', '/cart'],
    ['GET', '/checkout'],
    ['GET', '/orders'],
    ['GET', '/profile/edit'],
    ['GET', '/profile/koin'],
    ['GET', '/profile/wallet'],
    ['GET', '/profile/notifications'],
    ['GET', '/search'],
    ['GET', '/office'],
    ['GET', '/office/stores'],
    ['POST', '/logout'],
]);

test('UAT guest: halaman publik dapat diakses tanpa login', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/', '/login', '/register', '/forgot-password', '/robots.txt', '/sitemap.xml', '/menyusul']);
