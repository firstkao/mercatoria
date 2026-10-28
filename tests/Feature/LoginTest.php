<?php

use App\Models\Spammer;
use App\Models\User;

function loginPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ], $overrides);
}

test('guest dapat melihat halaman login', function () {
    $this->get('/login')->assertOk();
});

test('user dapat login dengan kredensial valid', function () {
    $user = User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
        'last_login' => null,
    ]);

    $this->post('/login', loginPayload())
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login)->not->toBeNull();
});

test('password salah tidak bisa login', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]);

    $this->post('/login', loginPayload(['password' => 'salah']))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('email tidak terdaftar tidak bisa login', function () {
    $this->post('/login', loginPayload(['email' => 'tidakada@example.com']))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('checkbox ingat saya menyimpan remember token', function () {
    $user = User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
        'remember_token' => null,
    ]);

    $this->post('/login', loginPayload(['remember' => '1']))
        ->assertRedirect('/');

    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('akun terkunci 15 menit setelah 5 kali gagal login', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]);

    foreach (range(1, 5) as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => "203.0.113.{$i}"])
            ->post('/login', loginPayload([
                'email' => 'budi@example.com',
                'password' => "salah-{$i}",
            ]))->assertSessionHasErrors('email');
    }

    $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.99'])
        ->post('/login', loginPayload([
            'email' => 'budi@example.com',
            'password' => 'password123',
        ]));

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('terkunci');
    $this->assertGuest();
});

test('login dibatasi 5 kali per menit', function () {
    $user = User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]);

    foreach (range(1, 5) as $i) {
        $this->post('/login', loginPayload([
            'email' => "lain{$i}@example.com",
            'password' => 'password123',
        ]))->assertSessionHasErrors('email');
    }

    $this->post('/login', loginPayload([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]))->assertStatus(429);
});

test('email di daftar spammer tidak bisa login', function () {
    Spammer::factory()->create([
        'email' => 'spammer@example.com',
        'status' => 'blocked',
    ]);

    $user = User::factory()->create([
        'email' => 'spammer@example.com',
        'password' => 'password123',
    ]);

    $this->post('/login', loginPayload([
        'email' => 'spammer@example.com',
        'password' => 'password123',
    ]))->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('honeypot terisi ditolak secara diam-diam', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]);

    $this->from('/login')->post('/login', loginPayload([
        'phone2' => '081234567890',
    ]))->assertRedirect('/login');

    $this->assertGuest();
});
