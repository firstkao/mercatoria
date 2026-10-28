<?php

use App\Models\Customer;
use App\Models\Spammer;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'tgl_lahir' => '1998-08-15',
        'no_hp' => '081234567890',
        'email' => 'budi@example.com',
        'provinsi' => 'DKI Jakarta',
        'kota' => 'Jakarta Selatan',
        'kecamatan' => 'Kebayoran Baru',
        'alamat' => 'Jl. Melati No. 10, RT 01 / RW 02',
        'kode_pos' => '12120',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'agree_tnc' => '1',
        'agree_privacy' => '1',
    ], $overrides);
}

test('guest dapat melihat halaman register', function () {
    $this->get('/register')->assertOk();
});

test('user dapat mendaftar dengan data valid', function () {
    $response = $this->post('/register', registerPayload([
        'email' => 'baru@example.com',
        'no_hp' => '081234567890',
    ]));

    $response->assertRedirect('/email/verify');

    $user = User::where('email', 'baru@example.com')->firstOrFail();
    expect($user->name)->toBe('Budi Santoso')
        ->and($user->role)->toBe('customer')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->is_migrated)->toBeFalse()
        ->and($user->has_seen_tour)->toBeFalse()
        ->and($user->no_hp)->toBe('6281234567890')
        ->and($user->referral_code)->not->toBeNull()
        ->and($user->tgl_lahir->toDateString())->toBe('1998-08-15');

    $this->assertAuthenticatedAs($user);
});

test('registrasi mengirim email verifikasi', function () {
    Notification::fake();

    $this->post('/register', registerPayload(['email' => 'verif@example.com']))
        ->assertRedirect('/email/verify');

    $user = User::where('email', 'verif@example.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('nama wajib diisi minimal 2 karakter', function () {
    $this->post('/register', registerPayload(['name' => 'B']))
        ->assertSessionHasErrors('name');

    $this->post('/register', registerPayload(['name' => '']))
        ->assertSessionHasErrors('name');
});

test('tanggal lahir wajib diisi', function () {
    $this->post('/register', registerPayload(['tgl_lahir' => '']))
        ->assertSessionHasErrors('tgl_lahir');
});

test('no hp wajib dengan format 08xxx atau 628xxx', function () {
    $this->post('/register', registerPayload(['no_hp' => '']))
        ->assertSessionHasErrors('no_hp');

    $this->post('/register', registerPayload(['no_hp' => '12345']))
        ->assertSessionHasErrors('no_hp');

    $this->post('/register', registerPayload([
        'email' => 'format@example.com',
        'no_hp' => '6281234567890',
    ]))->assertRedirect('/email/verify');

    expect(User::where('email', 'format@example.com')->firstOrFail()->no_hp)
        ->toBe('6281234567890');
});

test('email wajib valid dan unik', function () {
    $this->post('/register', registerPayload(['email' => '']))
        ->assertSessionHasErrors('email');

    $this->post('/register', registerPayload(['email' => 'bukan-email']))
        ->assertSessionHasErrors('email');

    User::factory()->create(['email' => 'sudah@example.com']);

    $this->post('/register', registerPayload(['email' => 'sudah@example.com']))
        ->assertSessionHasErrors('email');
});

test('password wajib minimal 8 karakter dan konfirmasi harus cocok', function () {
    $this->post('/register', registerPayload([
        'password' => 'pendek',
        'password_confirmation' => 'pendek',
    ]))->assertSessionHasErrors('password');

    $this->post('/register', registerPayload([
        'password' => 'password123',
        'password_confirmation' => 'beda',
    ]))->assertSessionHasErrors('password');
});

test('field alamat lengkap wajib diisi', function () {
    $this->post('/register', registerPayload([
        'provinsi' => '',
        'kota' => '',
        'kecamatan' => '',
        'alamat' => '',
        'kode_pos' => '',
    ]))
        ->assertSessionHasErrors('provinsi')
        ->assertSessionHasErrors('kota')
        ->assertSessionHasErrors('kecamatan')
        ->assertSessionHasErrors('alamat')
        ->assertSessionHasErrors('kode_pos');
});

test('checkbox syarat ketentuan dan kebijakan privasi wajib', function () {
    $this->post('/register', registerPayload([
        'agree_tnc' => null,
        'agree_privacy' => '1',
    ]))->assertSessionHasErrors('agree_tnc');

    $this->post('/register', registerPayload([
        'agree_tnc' => '1',
        'agree_privacy' => null,
    ]))->assertSessionHasErrors('agree_privacy');
});

test('buyer protection menolak email yang sudah menjadi pelanggan', function () {
    $customer = Customer::factory()->create([
        'email' => 'pelanggan@example.com',
        'no_hp' => '6281111111111',
    ]);

    $response = $this->from('/register')->post('/register', registerPayload([
        'email' => $customer->email,
        'no_hp' => '0822222222222',
    ]));

    $response->assertRedirect('/register')
        ->assertSessionHas('buyer_protection', true);

    expect(User::where('email', $customer->email)->exists())->toBeFalse();
});

test('buyer protection menolak no hp yang sudah menjadi pelanggan', function () {
    $customer = Customer::factory()->create([
        'email' => 'lain@example.com',
        'no_hp' => '6281111111111',
    ]);

    $response = $this->from('/register')->post('/register', registerPayload([
        'email' => 'baru2@example.com',
        'no_hp' => '081111111111',
    ]));

    $response->assertRedirect('/register')
        ->assertSessionHas('buyer_protection', true);

    expect(User::where('email', 'baru2@example.com')->exists())->toBeFalse();
});

test('maksimal 3 kali mendaftar dengan no hp yang sama', function () {
    User::factory()->count(2)->create(['no_hp' => '6285555555555']);

    $this->post('/register', registerPayload([
        'email' => 'ketiga@example.com',
        'no_hp' => '085555555555',
    ]))->assertRedirect('/email/verify');

    $this->post('/logout');

    $this->post('/register', registerPayload([
        'email' => 'keempat@example.com',
        'no_hp' => '085555555555',
    ]))->assertSessionHasErrors('no_hp');

    expect(User::where('email', 'keempat@example.com')->exists())->toBeFalse();
});

test('email yang masuk daftar spammer tidak bisa mendaftar', function () {
    Spammer::factory()->create([
        'email' => 'spammer@example.com',
        'no_hp' => null,
        'status' => 'blocked',
    ]);

    $this->post('/register', registerPayload([
        'email' => 'spammer@example.com',
        'no_hp' => '0877777777777',
    ]))->assertSessionHasErrors('email');

    expect(User::where('email', 'spammer@example.com')->exists())->toBeFalse();
});

test('no hp yang masuk daftar spammer tidak bisa mendaftar', function () {
    Spammer::factory()->create([
        'email' => null,
        'no_hp' => '6287777777777',
        'status' => 'blocked',
    ]);

    $this->post('/register', registerPayload([
        'email' => 'aman@example.com',
        'no_hp' => '087777777777',
    ]))->assertSessionHasErrors('no_hp');

    expect(User::where('email', 'aman@example.com')->exists())->toBeFalse();
});

test('honeypot terisi ditolak secara diam-diam', function () {
    $this->from('/register')->post('/register', registerPayload([
        'email' => 'bot@example.com',
        'website' => 'https://spam.example.com',
    ]))->assertRedirect('/register');

    expect(User::where('email', 'bot@example.com')->exists())->toBeFalse();
    $this->assertGuest();
});

test('kode undangan valid menautkan referrer', function () {
    $referrer = User::factory()->create(['referral_code' => 'MER-REF-8821']);

    $this->post('/register', registerPayload([
        'email' => 'referee@example.com',
        'referral_code' => 'MER-REF-8821',
    ]))->assertRedirect('/email/verify');

    $referee = User::where('email', 'referee@example.com')->firstOrFail();
    expect($referee->referred_by)->toBe($referrer->id);
});

test('kode undangan tidak valid ditolak', function () {
    $this->post('/register', registerPayload([
        'email' => 'referee2@example.com',
        'referral_code' => 'MER-REF-0000',
    ]))->assertSessionHasErrors('referral_code');

    expect(User::where('email', 'referee2@example.com')->exists())->toBeFalse();
});

test('referral code memakai format MER-REF saat registrasi', function () {
    $this->post('/register', registerPayload(['email' => 'kode@example.com']))
        ->assertRedirect('/email/verify');

    expect(User::where('email', 'kode@example.com')->firstOrFail()->referral_code)
        ->toMatch('/^MER-REF-\d{4}$/');
});

test('register dibatasi 3 kali per menit', function () {
    foreach (range(1, 3) as $i) {
        $this->post('/register', registerPayload([
            'email' => "burst{$i}@example.com",
            'no_hp' => '081234567'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
        ]))->assertRedirect('/email/verify');

        $this->post('/logout');
    }

    $this->post('/register', registerPayload([
        'email' => 'burst4@example.com',
        'no_hp' => '081234567004',
    ]))->assertStatus(429);
});
