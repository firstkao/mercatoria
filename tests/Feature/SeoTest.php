<?php

use App\Models\Game;
use App\Models\Product;

test('landing memuat meta og dan twitter card', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('og:title')
        ->toContain('og:description')
        ->toContain('og:image')
        ->toContain('og:url')
        ->toContain('twitter:card');
});

test('landing memuat json ld organization dan product', function () {
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'name' => 'Genshin Impact', 'is_active' => true]);
    Product::factory()->create(['game_id' => $game->id, 'name' => 'Cosmic Fragrance', 'slug' => 'cosmic-fragrance', 'is_active' => true]);

    $html = $this->get('/')->getContent();

    expect($html)->toContain('application/ld+json')
        ->toContain('Organization')
        ->toContain('Product');
});

test('sitemap xml dapat diakses berisi url game', function () {
    Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('xml')
        ->and($response->getContent())->toContain('/genshin-impact');
});

test('robots txt melarang path sensitif', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /office')
        ->assertSee('Disallow: /cart')
        ->assertSee('Disallow: /checkout')
        ->assertSee('Disallow: /profile')
        ->assertSee('Disallow: /orders');
});

test('halaman cms memiliki meta description', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('name="description"', false);
});

test('command sitemap:generate menulis public sitemap xml', function () {
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    Product::factory()->create(['game_id' => $game->id, 'slug' => 'cosmic-fragrance', 'is_active' => true]);

    $this->artisan('sitemap:generate')->assertSuccessful();

    $path = public_path('sitemap.xml');
    expect(file_exists($path))->toBeTrue();

    $content = (string) file_get_contents($path);
    expect($content)->toContain('<urlset')
        ->toContain('/genshin-impact/cosmic-fragrance');

    @unlink($path);
});
