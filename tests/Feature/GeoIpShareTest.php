<?php

use App\Models\Coin;
use App\Models\ShareClick;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

function fakePosition(string $ip, string $countryCode): Position
{
    $position = new Position;
    $position->countryCode = $countryCode;

    return $position;
}

test('geo ip indonesia memberi koin share', function () {
    Http::fake();

    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    Location::fake(['103.28.12.1' => fakePosition('103.28.12.1', 'ID')]);

    $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
        ->actingAs($user)
        ->post("/share/{$product->slug}");

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5);
});

test('geo ip luar negeri tidak memberi koin', function () {
    Http::fake();

    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    Location::fake(['8.8.8.8' => fakePosition('8.8.8.8', 'US')]);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->actingAs($user)
        ->post("/share/{$product->slug}");

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(0)
        ->and(ShareClick::count())->toBe(1);
});

test('klik ip sama tidak menggandakan koin', function () {
    Http::fake();

    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 0]);
    $product = productFinal255();

    Location::fake(['103.28.12.1' => fakePosition('103.28.12.1', 'ID')]);

    foreach ([1, 2] as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => '103.28.12.1'])
            ->actingAs($user)
            ->post("/share/{$product->slug}");
    }

    expect(Coin::where('user_id', $user->id)->value('balance'))->toBe(5);
});
