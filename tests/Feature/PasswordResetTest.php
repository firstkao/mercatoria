<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('guest dapat melihat form lupa password', function () {
    $this->get('/forgot-password')->assertOk();
});

test('email terdaftar menerima link reset password', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'budi@example.com']);

    $this->from('/forgot-password')
        ->post('/forgot-password', ['email' => 'budi@example.com'])
        ->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('email tidak terdaftar tetap mendapat respons sukses', function () {
    Notification::fake();

    $this->from('/forgot-password')
        ->post('/forgot-password', ['email' => 'tidakada@example.com'])
        ->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

test('user dapat mengatur ulang password dengan token valid', function () {
    $user = User::factory()->create(['email' => 'budi@example.com']);
    $token = Password::broker()->createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => 'budi@example.com',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertRedirect('/login');

    $this->assertGuest();

    $this->post('/login', [
        'email' => 'budi@example.com',
        'password' => 'password-baru',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('token reset password tidak valid ditolak', function () {
    $user = User::factory()->create(['email' => 'budi@example.com']);

    $this->post('/reset-password', [
        'token' => 'token-salah',
        'email' => 'budi@example.com',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
