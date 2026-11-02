<?php

use App\Models\Coin;
use App\Models\Game;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;

test('homepage logged-in tampil sesuai desain', function () {
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 550000]);

    $game = Game::factory()->create(['name' => 'Zenless Zone Zero', 'slug' => 'zenless-zone-zero', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Flint Workshop Series Chibi Acrylic Keychain',
        'slug' => 'flint-keychain',
        'is_active' => true,
        'price_rmb' => 50,
        'weight_gram' => 100,
    ]);
    $presale = Tag::create(['name' => 'Presale', 'slug' => 'presale']);
    $product->tags()->attach($presale->id);

    $response = $this->actingAs($user)->get('/')->assertOk();

    $response->assertSee('Konfirmasi Pembayaran')
        ->assertSee('Jelajahi Game Favoritmu')
        ->assertSee('Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori!')
        ->assertSee('MERCH BARU')
        ->assertSee('Zenless Zone Zero')
        ->assertSee('Flint Workshop Series Chibi Acrylic Keychain')
        ->assertSee('PRESALE')
        ->assertSee('Rp550.000')
        ->assertSee('Blacklist')
        ->assertSee('Reseller')
        ->assertSee('2022 MERCATORIA');
});
