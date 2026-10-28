<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('guest diarahkan ke login saat membuka halaman search', function () {
    $this->get('/search?q=stand')->assertRedirect('/login');
});

test('halaman search menampilkan hasil pencarian', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Tartaglia Figure',
        'slug' => 'tartaglia-figure',
    ]);
    Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Produk Lain',
        'slug' => 'produk-lain',
    ]);

    $this->actingAs($user)
        ->get('/search?q=Tartaglia')
        ->assertOk()
        ->assertSee('Tartaglia Figure')
        ->assertDontSee('Produk Lain');
});

test('hasil lewat nama varian menampilkan badge varian tersedia', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => '2023 Exhibition Series Acrylic Stand',
        'slug' => '2023-exhibition-series',
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Tartaglia',
    ]);

    $this->actingAs($user)
        ->get('/search?q=Tartaglia')
        ->assertOk()
        ->assertSee('Varian tersedia: Tartaglia');
});

test('kartu hasil varian menautkan parameter variant', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => '2023 Exhibition Series Acrylic Stand',
        'slug' => '2023-exhibition-series',
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Tartaglia',
    ]);

    $this->actingAs($user)
        ->get('/search?q=Tartaglia')
        ->assertOk()
        ->assertSee("variant={$variant->id}", false);
});
