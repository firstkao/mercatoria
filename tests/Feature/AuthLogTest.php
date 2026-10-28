<?php

use App\Models\AuthLog;
use App\Models\User;

test('login gagal tercatat di auth logs', function () {
    User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', ['email' => 'budi@example.com', 'password' => 'salah']);

    $log = AuthLog::where('email_attempt', 'budi@example.com')->firstOrFail();
    expect($log->action)->toBe('login_failed');
});

test('login sukses dan logout tercatat di auth logs', function () {
    $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'password123']);

    $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password123']);
    $this->post('/logout');

    expect(AuthLog::where('user_id', $user->id)->where('action', 'login_success')->exists())->toBeTrue()
        ->and(AuthLog::where('user_id', $user->id)->where('action', 'logout')->exists())->toBeTrue();
});

test('halaman auth logs dapat dibuka admin', function () {
    $admin = User::factory()->admin()->create();
    AuthLog::factory()->create(['email_attempt' => 'x@example.com', 'action' => 'login_failed']);

    $this->actingAs($admin)->get('/office/auth-logs')
        ->assertOk()
        ->assertSee('x@example.com');
});
