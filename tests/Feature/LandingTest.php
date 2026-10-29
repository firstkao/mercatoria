<?php

use App\Models\Game;
use App\Models\Product;

test('landing menampilkan section prd 40', function () {
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'name' => 'Genshin Impact', 'is_active' => true]);
    Product::factory()->create(['game_id' => $game->id, 'name' => 'Cosmic Fragrance', 'slug' => 'cosmic-fragrance', 'is_active' => true]);

    $html = $this->get('/')->getContent();

    // PRD 40: Top Promo Bar, Hero, Jelajahi Game, Merch Baru, Footer
    expect($html)->toContain('promo-bar')
        ->toContain('hero')
        ->toContain('Jelajahi Game')
        ->toContain('MERCH BARU')
        ->toContain('Genshin Impact')
        ->toContain('Cosmic Fragrance')
        ->toContain('footer');
});

test('landing menampilkan grid game aktif saja', function () {
    Game::factory()->create(['slug' => 'aktif-game', 'name' => 'Aktif Game', 'is_active' => true]);
    Game::factory()->create(['slug' => 'mati-game', 'name' => 'Mati Game', 'is_active' => false]);

    $html = $this->get('/')->getContent();

    expect($html)->toContain('Aktif Game')
        ->and($html)->not->toContain('Mati Game');
});

test('landing dapat diakses guest', function () {
    $this->get('/')->assertOk();
});
