<?php

use App\Models\User;
use App\Models\UserNotification;

test('referee yang mendaftar memberi bell ke referrer', function () {
    $referrer = User::factory()->create();

    $this->post('/register', registerPayload([
        'email' => 'referee-baru@example.com',
        'no_hp' => '081234567031',
        'referral_code' => $referrer->referral_code,
    ]))->assertRedirect();

    $bell = UserNotification::where('user_id', $referrer->id)->where('type', 'referral_joined')->sole();
    expect($bell->title)->toContain('menggunakan kode undanganmu')
        ->and($bell->title)->toContain('Budi Santoso')
        ->and($bell->target_url)->toBe('/profile/undang');
});

test('tanpa kode referral referrer tidak dapat bell', function () {
    $this->post('/register', registerPayload(['email' => 'tanpa-ref@example.com', 'no_hp' => '081234567032']))
        ->assertRedirect();

    expect(UserNotification::where('type', 'referral_joined')->exists())->toBeFalse();
});
