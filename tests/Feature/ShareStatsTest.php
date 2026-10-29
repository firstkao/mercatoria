<?php

use App\Models\CoinTransaction;
use App\Models\ShareClick;
use App\Models\User;

test('halaman undang menampilkan shareStats opens dan coins', function () {
    $user = User::factory()->create();
    ShareClick::factory()->count(3)->create(['user_id' => $user->id]);
    CoinTransaction::factory()->create(['user_id' => $user->id, 'type' => CoinTransaction::TYPE_EARNED_SHARE, 'amount' => 5]);
    CoinTransaction::factory()->create(['user_id' => $user->id, 'type' => CoinTransaction::TYPE_EARNED_SHARE, 'amount' => 5]);

    $response = $this->actingAs($user)->get('/profile/undang')->assertOk();

    $shareStats = $response->viewData('shareStats');

    expect($shareStats['opens'])->toBe(3)
        ->and($shareStats['coins'])->toBe(10);

    $html = $response->getContent();
    expect($html)->toContain('LINK DIBUKA')
        ->toContain('KOIN DARI SHARE')
        ->toContain('10');
});

test('shareStats nol saat belum ada aktivitas share', function () {
    $user = User::factory()->create();

    $shareStats = $this->actingAs($user)->get('/profile/undang')->assertOk()->viewData('shareStats');

    expect($shareStats['opens'])->toBe(0)
        ->and($shareStats['coins'])->toBe(0);
});

test('shareStats hanya menghitung milik user sendiri', function () {
    $user = User::factory()->create();
    $lain = User::factory()->create();
    ShareClick::factory()->count(2)->create(['user_id' => $user->id]);
    ShareClick::factory()->count(5)->create(['user_id' => $lain->id]);
    CoinTransaction::factory()->create(['user_id' => $lain->id, 'type' => CoinTransaction::TYPE_EARNED_SHARE, 'amount' => 50]);

    $shareStats = $this->actingAs($user)->get('/profile/undang')->assertOk()->viewData('shareStats');

    expect($shareStats['opens'])->toBe(2)
        ->and($shareStats['coins'])->toBe(0);
});

test('koin non-share tidak ikut hitungan shareStats', function () {
    $user = User::factory()->create();
    ShareClick::factory()->create(['user_id' => $user->id]);
    CoinTransaction::factory()->create(['user_id' => $user->id, 'type' => CoinTransaction::TYPE_EARNED_BIRTHDAY, 'amount' => 100]);

    $shareStats = $this->actingAs($user)->get('/profile/undang')->assertOk()->viewData('shareStats');

    expect($shareStats['opens'])->toBe(1)
        ->and($shareStats['coins'])->toBe(0);
});
