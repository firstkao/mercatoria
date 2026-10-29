<?php

use App\Models\Coin;
use App\Models\ShareClick;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

test('share produk memberi 5 koin per ip unik', function () {
    Http::fake();
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $position = new Position;
    $position->countryCode = 'ID';
    Location::fake(['103.28.12.1' => $position]);

    foreach ([1, 2] as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
            ->actingAs($user)
            ->post("/share/{$product->slug}")
            ->assertRedirect();
    }

    // IP sama → koin hanya sekali
    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5)
        ->and(ShareClick::count())->toBe(2);
});

test('share dari ip berbeda menambah koin lagi', function () {
    Http::fake();
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $id = new Position;
    $id->countryCode = 'ID';

    Location::fake(['103.28.12.1' => $id, '103.28.12.2' => $id]);

    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
        ->actingAs($user)->post("/share/{$product->slug}");
    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.2'])
        ->actingAs($user)->post("/share/{$product->slug}");

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(10);
});

test('ip luar indonesia tidak dapat koin', function () {
    Http::fake();
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    $us = new Position;
    $us->countryCode = 'US';
    Location::fake(['8.8.8.8' => $us]);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->actingAs($user)->post("/share/{$product->slug}");

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0)
        ->and(ShareClick::count())->toBe(1); // klik tetap tercatat untuk analitik
});
