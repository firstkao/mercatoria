<?php

use App\Models\Game;
use App\Models\Product;

test('homepage guest tampil dengan navbar footer dan tanpa menu konfirmasi pembayaran', function () {
    $game = Game::factory()->create(['name' => 'Zenless Zone Zero', 'slug' => 'zenless-zone-zero', 'is_active' => true]);
    Product::factory()->create(['game_id' => $game->id, 'name' => 'Chibi Acrylic Keychain', 'slug' => 'chibi-keychain', 'is_active' => true]);

    $response = $this->get('/')->assertOk();

    $response->assertSee('Login')
        ->assertSee('Daftar')
        ->assertSee('Jelajahi Game Favoritmu')
        ->assertSee('MERCH BARU')
        ->assertSee('id="footer"', false)
        ->assertSee('Produk Terpopuler')
        ->assertDontSee('Konfirmasi Pembayaran');
});

test('homepage guest tidak menampilkan data user', function () {
    $this->get('/')->assertOk()
        ->assertDontSee('Keranjang')
        ->assertDontSee(route('cart.index'));
});

test('homepage bot ringan tetap memuat konten utama untuk seo', function () {
    $game = Game::factory()->create(['name' => 'Genshin Impact', 'slug' => 'genshin-impact', 'is_active' => true]);
    Product::factory()->create(['game_id' => $game->id, 'name' => 'Acrylic Keychain', 'slug' => 'acrylic-keychain', 'is_active' => true]);

    $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
        ->get('/')
        ->assertOk();

    $response->assertSee('Jelajahi Game Favoritmu')
        ->assertSee('MERCH BARU')
        ->assertSee('Acrylic Keychain')
        ->assertSee('id="footer"', false)
        ->assertDontSee('hero-carousel');
});

test('navbar footer identik dipakai halaman auth', function () {
    $html = $this->get('/login')->assertOk()->getContent();

    expect($html)->toContain('Jelajahi')
        ->toContain('Produk Terpopuler')
        ->toContain('Login');
});
