<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserNotification;

test('koin expired 1 tahun dihapus dari saldo', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 3000]);

    CoinTransaction::create([
        'user_id' => $user->id,
        'type' => 'earned_order',
        'amount' => 3000,
        'balance_after' => 3000,
        'expired_at' => now()->subDay(),
    ]);

    $this->artisan('coin:expire')->assertSuccessful();

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0)
        ->and(CoinTransaction::where('user_id', $user->id)->where('type', 'expired')->exists())->toBeTrue();
});

test('koin belum expired tetap aktif', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 3000]);

    CoinTransaction::create([
        'user_id' => $user->id,
        'type' => 'earned_order',
        'amount' => 3000,
        'balance_after' => 3000,
        'expired_at' => now()->addMonth(),
    ]);

    $this->artisan('coin:expire')->assertSuccessful();

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(3000);
});

test('cron expiry notify memberi peringatan h-30', function () {
    $user = User::factory()->create();

    CoinTransaction::create([
        'user_id' => $user->id,
        'type' => 'earned_order',
        'amount' => 1000,
        'balance_after' => 1000,
        'expired_at' => now()->addDays(29),
    ]);

    $this->artisan('coin:expiry-notify')->assertSuccessful();

    expect(UserNotification::where('user_id', $user->id)->where('type', 'coin_expiring')->exists())->toBeTrue();
});
