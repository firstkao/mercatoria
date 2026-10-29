<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;

test('bonus ulang tahun memberi 5000 koin dan voucher', function () {
    $user = User::factory()->create(['tgl_lahir' => now()->subYears(25)->toDateString()]);

    $this->artisan('user:birthday-bonus')->assertSuccessful();

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5000)
        ->and(UserVoucher::where('user_id', $user->id)->count())->toBe(1)
        ->and(CoinTransaction::where('user_id', $user->id)->where('type', 'earned_birthday')->exists())->toBeTrue();
});

test('hanya user berulang tahun hari ini yang dapat', function () {
    $birthdayToday = User::factory()->create(['tgl_lahir' => now()->subYears(20)->toDateString()]);
    $other = User::factory()->create(['tgl_lahir' => now()->subYears(20)->addMonth()->toDateString()]);

    $this->artisan('user:birthday-bonus')->assertSuccessful();

    expect(Coin::where('user_id', $birthdayToday->id)->value('balance'))->toBe(5000)
        ->and(CoinTransaction::where('user_id', $other->id)->where('type', 'earned_birthday')->exists())->toBeFalse();
});

test('bonus ulang tahun tidak dobel di hari yang sama', function () {
    $user = User::factory()->create(['tgl_lahir' => now()->subYears(22)->toDateString()]);

    $this->artisan('user:birthday-bonus');
    $this->artisan('user:birthday-bonus');

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5000)
        ->and(CoinTransaction::where('user_id', $user->id)->where('type', 'earned_birthday')->count())->toBe(1);
});
