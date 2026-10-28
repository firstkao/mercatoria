<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;

test('halaman koin menampilkan saldo dan riwayat', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 5600]);
    CoinTransaction::create([
        'user_id' => $user->id,
        'order_id' => null,
        'type' => 'earned_order',
        'amount' => 5600,
        'balance_after' => 5600,
        'expired_at' => now()->addYear(),
    ]);

    $this->actingAs($user)->get('/profile/koin')
        ->assertOk()
        ->assertSee('TOTAL KOIN AKTIF')
        ->assertSee('5.600')
        ->assertSee('earned_order');
});

test('halaman wallet menampilkan voucher aktif', function () {
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'is_active' => true]);
    UserVoucher::factory()->create([
        'user_id' => $user->id,
        'voucher_id' => $voucher->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)->get('/profile/wallet')
        ->assertOk()
        ->assertSee('25')
        ->assertSee($voucher->code);
});

test('halaman undang teman menampilkan kode referral', function () {
    $user = User::factory()->create(['referral_code' => 'MER-REF-1234']);

    $this->actingAs($user)->get('/profile/undang')
        ->assertOk()
        ->assertSee('MER-REF-1234');
});

test('halaman koin dan wallet butuh login', function () {
    $this->get('/profile/koin')->assertRedirect('/login');
    $this->get('/profile/wallet')->assertRedirect('/login');
    $this->get('/profile/undang')->assertRedirect('/login');
});
