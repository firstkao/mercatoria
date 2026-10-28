<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;

test('guest diarahkan ke login saat membuka katalog', function () {
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);

    $this->get('/genshin-impact')->assertRedirect('/login');
});

test('user dapat melihat katalog game', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'name' => 'Genshin Impact', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Cosmic Fragrance',
        'slug' => 'cosmic-fragrance',
        'price_rmb' => 100,
        'weight_gram' => 0,
    ]);

    $this->actingAs($user)
        ->get('/genshin-impact')
        ->assertOk()
        ->assertSee('Genshin Impact')
        ->assertSee('Cosmic Fragrance');
});

test('katalog menampilkan harga bersih hasil kalkulasi', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    // 58 RMB · store min 100 ongkir 7 · 50g → harga bersih Rp215.000
    $store = Store::factory()->create(['min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
    Product::factory()->create([
        'game_id' => $game->id,
        'store_id' => $store->id,
        'name' => 'Cosmic Fragrance',
        'slug' => 'cosmic-fragrance',
        'price_rmb' => 58,
        'weight_gram' => 50,
    ]);

    $this->actingAs($user)
        ->get('/genshin-impact')
        ->assertOk()
        ->assertSee('Rp215.000');
});

test('ribbon tag presale tampil di kartu produk', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'honkai-star-rail', 'is_active' => true]);
    $tag = Tag::factory()->create(['name' => 'PRESALE', 'slug' => 'presale']);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Fluffy Keychain',
        'slug' => 'fluffy-keychain',
    ]);
    $product->tags()->attach($tag->id);

    $this->actingAs($user)
        ->get('/honkai-star-rail')
        ->assertOk()
        ->assertSee('PRESALE');
});

test('user dapat melihat detail produk dengan varian', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Acrylic Stand Series',
        'slug' => 'acrylic-stand-series',
        'type' => 'variant',
        'price_rmb' => 35,
        'weight_gram' => 200,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Tartaglia',
        'sku' => 'AS-TART-001',
    ]);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Alhaitham',
        'sku' => 'AS-ALH-001',
    ]);

    $this->actingAs($user)
        ->get('/genshin-impact/acrylic-stand-series')
        ->assertOk()
        ->assertSee('Acrylic Stand Series')
        ->assertSee('Tartaglia')
        ->assertSee('Alhaitham');
});

test('detail produk merespon parameter variant', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Acrylic Stand Series',
        'slug' => 'acrylic-stand-series',
        'type' => 'variant',
        'price_rmb' => 35,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Tartaglia',
        'price_rmb' => 58,
    ]);

    $this->actingAs($user)
        ->get("/genshin-impact/acrylic-stand-series?variant={$variant->id}")
        ->assertOk()
        ->assertSee('Tartaglia');
});

test('katalog per kategori merch tampil', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    $category = MerchCategory::factory()->create(['name' => 'Plush', 'slug' => 'plush']);
    Product::factory()->create([
        'game_id' => $game->id,
        'merch_category_id' => $category->id,
        'name' => 'Paimon Plushie',
        'slug' => 'paimon-plushie',
    ]);

    $this->actingAs($user)
        ->get('/genshin-impact/kategori/plush')
        ->assertOk()
        ->assertSee('Paimon Plushie');
});

test('katalog developer tampil', function () {
    $user = User::factory()->create();
    $developer = Developer::factory()->create(['name' => 'miHoYo', 'slug' => 'mihoyo']);
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'name' => 'Genshin Impact', 'is_active' => true]);
    $game->developers()->attach($developer->id);
    Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Cosmic Fragrance',
        'slug' => 'cosmic-fragrance',
    ]);

    $this->actingAs($user)
        ->get('/developer/mihoyo')
        ->assertOk()
        ->assertSee('Cosmic Fragrance');
});

test('produk nonaktif tidak tampil di katalog', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'is_active' => true]);
    Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Produk Mati',
        'slug' => 'produk-mati',
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->get('/genshin-impact')
        ->assertOk()
        ->assertDontSee('Produk Mati');
});
