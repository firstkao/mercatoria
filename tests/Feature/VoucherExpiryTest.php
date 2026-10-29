<?php

use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserVoucher;
use App\Notifications\VoucherExpiringNotification;
use Illuminate\Support\Facades\Notification;

test('voucher akan expired H-30 memicu bell dan email', function () {
    Notification::fake();
    $user = User::factory()->create();
    UserVoucher::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'expires_at' => now()->addDays(10),
    ]);

    $this->artisan('coin:expiry-notify')->assertSuccessful();

    $bell = UserNotification::where('user_id', $user->id)->where('type', 'voucher_expiring')->firstOrFail();
    expect($bell->title)->toContain('Voucher kamu akan expired pada');
    Notification::assertSentTo($user, VoucherExpiringNotification::class);

    // Voucher yang belum dekat masa expired-nya tidak diperingatkan
    $user2 = User::factory()->create();
    UserVoucher::factory()->create([
        'user_id' => $user2->id,
        'status' => 'active',
        'expires_at' => now()->addDays(60),
    ]);
    $user3 = User::factory()->create();
    UserVoucher::factory()->create([
        'user_id' => $user3->id,
        'status' => 'used_at_checkout',
        'expires_at' => now()->addDays(5),
    ]);

    $this->artisan('coin:expiry-notify')->assertSuccessful();

    expect(UserNotification::where('user_id', $user2->id)->where('type', 'voucher_expiring')->exists())->toBeFalse()
        ->and(UserNotification::where('user_id', $user3->id)->where('type', 'voucher_expiring')->exists())->toBeFalse();
});
