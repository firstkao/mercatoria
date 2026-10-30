<?php

use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\User;

test('halaman undang teman menampilkan stats undangan', function () {
    $referrer = User::factory()->create();

    $refereeBerhasil = User::factory()->create(['referred_by' => $referrer->id]);
    Order::factory()->create(['user_id' => $refereeBerhasil->id, 'status' => 'selesai']);

    User::factory()->create(['referred_by' => $referrer->id]);

    CoinTransaction::factory()->create([
        'user_id' => $referrer->id,
        'type' => CoinTransaction::TYPE_EARNED_REFERRAL_REFERRER,
        'amount' => 3000,
    ]);

    $response = $this->actingAs($referrer)->get('/profile/undang')->assertOk();

    $response->assertSee('Total Diundang');

    expect($response->viewData('referralStats')['total_diundang'])->toBe(2)
        ->and($response->viewData('referralStats')['berhasil'])->toBe(1)
        ->and($response->viewData('referralStats')['menunggu_order'])->toBe(1)
        ->and($response->viewData('referralStats')['koin_didapat'])->toBe(3000);
});
