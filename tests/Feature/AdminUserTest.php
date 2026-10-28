<?php

use App\Models\AdminLog;
use App\Models\User;

test('admin membuat user baru', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/users', [
        'name' => 'User Inject',
        'email' => 'inject@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'customer',
        'tgl_lahir' => '1995-05-05',
        'no_hp' => '089999999999',
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar Barat',
        'alamat' => 'Jalan Inject 1',
        'kode_pos' => '80111',
    ])->assertRedirect();

    $user = User::where('email', 'inject@example.com')->firstOrFail();
    expect($user->role)->toBe('customer')
        ->and($user->is_migrated)->toBeTrue();
});

test('admin mengubah data user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($admin)->put("/office/users/{$user->id}", [
        'name' => 'Nama Diubah Admin',
        'email' => $user->email,
        'role' => 'customer',
    ])->assertRedirect();

    expect($user->fresh()->name)->toBe('Nama Diubah Admin');
});

test('perubahan user oleh admin tercatat', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->put("/office/users/{$user->id}", [
        'name' => 'Tercatat',
        'email' => $user->email,
        'role' => 'customer',
    ]);

    expect(AdminLog::where('model', 'User')->where('model_id', $user->id)->exists())->toBeTrue();
});
