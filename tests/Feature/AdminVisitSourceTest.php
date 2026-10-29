<?php

use App\Models\User;
use App\Models\VisitorTracking;

test('detail user dan order admin menampilkan sumber referral dan landing page', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    VisitorTracking::create([
        'source' => 'twitter.com',
        'medium' => 'referral',
        'landing_page' => 'https://mercatoria.id/promo',
        'user_id' => $user->id,
    ]);
    [, $order] = placeTestOrder($user);

    $visit = $this->actingAs($admin)->get("/office/users/{$user->id}/edit")
        ->assertOk()
        ->viewData('visit');

    expect($visit['source'])->toBe('twitter.com')
        ->and($visit['landing_page'])->toBe('https://mercatoria.id/promo');

    $visitOrder = $this->actingAs($admin)->get("/office/orders/{$order->no_order}")
        ->assertOk()
        ->viewData('visit');

    expect($visitOrder['source'])->toBe('twitter.com')
        ->and($visitOrder['landing_page'])->toBe('https://mercatoria.id/promo');
});
