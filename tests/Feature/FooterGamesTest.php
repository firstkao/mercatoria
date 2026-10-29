<?php

use App\Models\Developer;
use App\Models\Game;

test('footer menampilkan daftar game dan developer dari view composer', function () {
    Game::factory()->create(['name' => 'Game Footer Satu', 'slug' => 'game-footer-satu', 'is_active' => true]);
    Game::factory()->create(['name' => 'Game Mati', 'slug' => 'game-mati', 'is_active' => false]);
    Developer::factory()->create(['name' => 'Dev Footer Satu', 'slug' => 'dev-footer-satu', 'is_active' => true]);

    // Halaman ber-layout guest — footernya membaca $footerGames / $footerDevelopers
    $html = $this->get('/login')->assertOk()->getContent();

    expect($html)->toContain('Game Footer Satu')
        ->toContain('Dev Footer Satu')
        ->and($html)->not->toContain('Game Mati');
});
