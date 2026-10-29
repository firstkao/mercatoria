<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('turnstile aktif menolak login tanpa token', function () {
    config(['services.turnstile.secret' => 'test-secret', 'services.turnstile.site_key' => 'test-site']);
    Http::fake();

    User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'cf-turnstile-response' => '',
    ])->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

test('turnstile menolak token tidak valid', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Http::fake(['*cloudflare.com/*' => Http::response(['success' => false])]);

    User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'cf-turnstile-response' => 'token-salah',
    ])->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

test('turnstile menerima token valid', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Http::fake(['*cloudflare.com/*' => Http::response(['success' => true])]);

    User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'cf-turnstile-response' => 'token-valid',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('tanpa secret turnstile tidak diperiksa', function () {
    config(['services.turnstile.secret' => null]);
    Http::fake();

    User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
    ])->assertRedirect('/dashboard');

    Http::assertNothingSent();
});

test('register juga dilindungi turnstile', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Http::fake();

    $this->post('/register', registerPayload(['email' => 'baru@example.com']))
        ->assertSessionHasErrors('cf-turnstile-response');

    expect(User::where('email', 'baru@example.com')->exists())->toBeFalse();
});

test('reset password wajib turnstile saat aktif', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Http::fake();

    $this->post('/forgot-password', [
        'email' => 'budi@example.com',
        'cf-turnstile-response' => '',
    ])->assertSessionHasErrors('cf-turnstile-response');

    $this->post('/reset-password', [
        'token' => 'token-x',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => '',
    ])->assertSessionHasErrors('cf-turnstile-response');
});
