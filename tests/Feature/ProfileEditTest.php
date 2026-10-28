<?php

use App\Models\User;

test('user dapat melihat dan mengubah profil', function () {
    $user = User::factory()->create(['name' => 'Nama Lama', 'kota' => 'Bandung']);

    $this->actingAs($user)->get('/profile/edit')
        ->assertOk()
        ->assertSee('Nama Lama');

    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Nama Baru',
        'email' => $user->email,
        'no_hp' => '081234567890',
        'provinsi' => 'DKI Jakarta',
        'kota' => 'Jakarta Selatan',
        'kecamatan' => 'Kebayoran Baru',
        'alamat' => 'Jalan Baru 1',
        'kode_pos' => '12120',
    ])->assertRedirect('/profile/edit');

    $user->refresh();
    expect($user->name)->toBe('Nama Baru')
        ->and($user->kota)->toBe('Jakarta Selatan')
        ->and($user->no_hp)->toBe('6281234567890');
});

test('tanggal lahir dan kode undangan terkunci', function () {
    $user = User::factory()->create(['tgl_lahir' => '1998-08-15']);

    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Tetap',
        'email' => $user->email,
        'tgl_lahir' => '2000-01-01',
        'no_hp' => $user->no_hp,
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar',
        'alamat' => 'Jalan',
        'kode_pos' => '80000',
    ])->assertRedirect('/profile/edit');

    expect($user->fresh()->tgl_lahir->toDateString())->toBe('1998-08-15');
});

test('user dapat mengganti password', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'password123',
        'password' => 'password-baru-1',
        'password_confirmation' => 'password-baru-1',
    ])->assertRedirect('/profile/edit');

    $this->post('/logout');

    $this->post('/login', ['email' => $user->email, 'password' => 'password-baru-1'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('ganti password menolak password lama salah', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'salah',
        'password' => 'password-baru-1',
        'password_confirmation' => 'password-baru-1',
    ])->assertSessionHasErrors('current_password');
});

test('form lengkapi profil untuk tgl lahir kosong', function () {
    $user = User::factory()->create(['tgl_lahir' => '2000-01-01']);
    $user->forceFill(['tgl_lahir' => null])->save();

    $this->actingAs($user)->get('/profile/complete')->assertOk();

    $this->actingAs($user)->post('/profile/complete', [
        'tgl_lahir' => '1995-05-05',
    ])->assertRedirect('/profile');

    expect($user->fresh()->tgl_lahir->toDateString())->toBe('1995-05-05');
});
