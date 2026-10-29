<?php

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('inject user lama boleh tanpa password dan tgl lahir', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/users', [
        'name' => 'User Lama',
        'email' => 'lama@example.com',
        'no_hp' => '6281234567890',
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar Barat',
        'alamat' => 'Jl. Lama No. 1',
        'kode_pos' => '80111',
    ])->assertRedirect();

    $user = User::where('email', 'lama@example.com')->firstOrFail();

    expect($user->is_migrated)->toBeTrue()
        ->and($user->role)->toBe('customer')
        ->and($user->tgl_lahir)->toBeNull()
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->password)->not->toBe('')
        ->and(Hash::check('password123', $user->password))->toBeFalse();
});

test('password dan tgl lahir tetap terisi saat diberikan', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/users', [
        'name' => 'User Baru',
        'email' => 'baru@example.com',
        'no_hp' => '6281234567891',
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar',
        'alamat' => 'Jl. Baru No. 2',
        'kode_pos' => '80222',
        'password' => 'password123',
        'tgl_lahir' => '1998-08-15',
    ])->assertRedirect();

    $user = User::where('email', 'baru@example.com')->firstOrFail();

    expect($user->tgl_lahir)->not->toBeNull()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('force reset password mengirim link reset ke email user', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/office/users/{$user->id}/force-reset-password")->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class);
    expect(AdminLog::where('model', 'User')->where('action', 'force_reset_password')->exists())->toBeTrue();
});

test('force verify email mengeset email verified', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->unverified()->create();

    $this->actingAs($admin)->post("/office/users/{$user->id}/force-verify-email")->assertRedirect();

    expect($user->fresh()->email_verified_at)->not->toBeNull()
        ->and(AdminLog::where('model', 'User')->where('action', 'force_verify_email')->exists())->toBeTrue();
});

test('non-admin ditolak 403 force reset dan verify', function () {
    $customer = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($customer)->post("/office/users/{$user->id}/force-reset-password")->assertForbidden();
    $this->actingAs($customer)->post("/office/users/{$user->id}/force-verify-email")->assertForbidden();
});

test('user hasil inject tidak bisa login sebelum set password', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/users', [
        'name' => 'User Rahasia',
        'email' => 'rahasia@example.com',
        'no_hp' => '6281234567892',
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar',
        'alamat' => 'Jl. Rahasia No. 3',
        'kode_pos' => '80333',
    ]);

    auth()->logout();

    $this->post('/login', ['email' => 'rahasia@example.com', 'password' => 'password123'])
        ->assertSessionHasErrors();
    $this->assertGuest();
});
