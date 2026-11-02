<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use App\Services\Product\ProductSearchService;

function searchService(): ProductSearchService
{
    return app(ProductSearchService::class);
}

test('cari dari nama produk', function () {
    $target = Product::factory()->create(['name' => 'Cosmic Fragrance']);
    Product::factory()->create(['name' => 'Fluffy Acrylic Stand']);

    $result = searchService()->search('Cosmic');

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($target->id);
});

test('cari dari nama varian menemukan produk induk', function () {
    $product = Product::factory()->create(['name' => '2023 Exhibition Series Acrylic Stand']);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Tartaglia',
    ]);
    Product::factory()->create(['name' => 'Produk Lain']);

    $result = searchService()->search('Tartaglia');

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($product->id);
});

test('cari dari sku varian', function () {
    $product = Product::factory()->create(['name' => 'Acrylic Stand']);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Alhaitham',
        'sku' => 'VAR-ALH-001',
    ]);

    $result = searchService()->search('VAR-ALH');

    expect($result->total())->toBe(1);
});

test('cari dari nama game dan developer', function () {
    $developer = Developer::factory()->create(['name' => 'HyperGryph']);
    $game = Game::factory()->create(['name' => 'Arknights: Endfield']);
    $game->developers()->attach($developer->id);
    $product = Product::factory()->create([
        'name' => 'Merchandise Biasa',
        'game_id' => $game->id,
        'developer_id' => $developer->id,
    ]);

    expect(searchService()->search('Endfield')->total())->toBe(1)
        ->and(searchService()->search('HyperGryph')->total())->toBe(1);
});

test('produk nonaktif tidak muncul di hasil pencarian', function () {
    Product::factory()->create(['name' => 'Produk Aktif', 'is_active' => true]);
    Product::factory()->create(['name' => 'Produk Mati', 'is_active' => false]);

    expect(searchService()->search('Produk')->total())->toBe(1);
});

test('filter berdasarkan game', function () {
    $gameA = Game::factory()->create();
    $gameB = Game::factory()->create();
    Product::factory()->create(['game_id' => $gameA->id, 'name' => 'Item Alpha']);
    Product::factory()->create(['game_id' => $gameB->id, 'name' => 'Item Alpha']);

    $result = searchService()->search('Item', ['game_id' => $gameA->id]);

    expect($result->total())->toBe(1);
});

test('filter berdasarkan tag', function () {
    $presale = Tag::factory()->create(['name' => 'PRESALE', 'slug' => 'presale']);
    $limited = Tag::factory()->create(['name' => 'LIMITED', 'slug' => 'limited']);
    $target = Product::factory()->create(['name' => 'Item Alpha']);
    $target->tags()->attach($presale->id);
    $other = Product::factory()->create(['name' => 'Item Beta']);
    $other->tags()->attach($limited->id);

    $result = searchService()->search('Item', ['tag' => 'presale']);

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($target->id);
});

test('filter berdasarkan kategori merch', function () {
    $category = MerchCategory::factory()->create(['slug' => 'plush']);
    Product::factory()->create(['name' => 'Item Alpha', 'merch_category_id' => $category->id]);
    Product::factory()->create(['name' => 'Item Beta', 'merch_category_id' => null]);

    $result = searchService()->search('Item', ['merch_category_id' => $category->id]);

    expect($result->total())->toBe(1);
});

test('hasil cocok di judul produk diurutkan lebih dulu', function () {
    $viaVariant = Product::factory()->create(['name' => '2023 Exhibition Series']);
    ProductVariant::factory()->create([
        'product_id' => $viaVariant->id,
        'name' => 'Tartaglia Ver.',
    ]);
    $viaNama = Product::factory()->create(['name' => 'Tartaglia Figure']);

    $result = searchService()->search('Tartaglia');

    expect($result->total())->toBe(2)
        ->and($result->first()->id)->toBe($viaNama->id)
        ->and($result->last()->id)->toBe($viaVariant->id);
});
