<?php

use App\Models\Coin;
use App\Models\ShareClick;
use App\Models\User;

test('share produk memberi 5 koin per ip unik', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
        ->actingAs($user)
        ->post("/share/{$product->slug}", ['country_code' => 'ID'])
        ->assertRedirect();

    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
        ->actingAs($user)
        ->post("/share/{$product->slug}", ['country_code' => 'ID'])
        ->assertRedirect();

    // IP sama → koin hanya sekali
    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5)
        ->and(ShareClick::count())->toBe(2);
});

test('share dari ip berbeda menambah koin lagi', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
        ->actingAs($user)->post("/share/{$product->slug}", ['country_code' => 'ID']);
    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.2'])
        ->actingAs($user)->post("/share/{$product->slug}", ['country_code' => 'ID']);

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(10);
});

test('ip luar indonesia tidak dapat koin', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->actingAs($user)->post("/share/{$product->slug}", ['country_code' => 'US']);

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0)
        ->and(ShareClick::count())->toBe(1); // klik tetap tercatat untuk analitik
});
