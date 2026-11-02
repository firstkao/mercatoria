<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function catalogNames($response): array
{
    return $response->viewData('products')->pluck('name')->all();
}

beforeEach(function () {
    $this->game = Game::factory()->create(['is_active' => true]);
    $this->user = User::factory()->create();
    $this->cheap = Product::factory()->create(['game_id' => $this->game->id, 'name' => 'Beta Murah', 'price_rmb' => 10, 'sold_count' => 1, 'availability' => 'tersedia', 'is_active' => true]);
    $this->mid = Product::factory()->create(['game_id' => $this->game->id, 'name' => 'Alpha Tengah', 'price_rmb' => 100, 'sold_count' => 50, 'availability' => 'kosong', 'is_active' => true]);
    $this->pricey = Product::factory()->create(['game_id' => $this->game->id, 'name' => 'Gamma Mahal', 'price_rmb' => 500, 'sold_count' => 10, 'availability' => 'tersedia', 'is_active' => true]);
});

test('sort katalog: termurah, termahal, terpopuler, terbaru, abjad', function () {
    $url = fn ($sort) => route('catalog.game', ['slug' => $this->game->slug, 'sort' => $sort]);

    expect(catalogNames($this->actingAs($this->user)->get($url('termurah'))))->toBe(['Beta Murah', 'Alpha Tengah', 'Gamma Mahal']);
    expect(catalogNames($this->actingAs($this->user)->get($url('termahal'))))->toBe(['Gamma Mahal', 'Alpha Tengah', 'Beta Murah']);
    expect(catalogNames($this->actingAs($this->user)->get($url('terpopuler'))))->toBe(['Alpha Tengah', 'Gamma Mahal', 'Beta Murah']);
    expect(catalogNames($this->actingAs($this->user)->get($url('terbaru'))))->toBe(['Gamma Mahal', 'Alpha Tengah', 'Beta Murah']);
    expect(catalogNames($this->actingAs($this->user)->get($url('abjad'))))->toBe(['Alpha Tengah', 'Beta Murah', 'Gamma Mahal']);
});

test('filter ketersediaan dan tag pada katalog', function () {
    $tag = Tag::factory()->create(['slug' => 'presale']);
    $this->cheap->tags()->attach($tag);

    $base = ['slug' => $this->game->slug];
    expect(catalogNames($this->actingAs($this->user)->get(route('catalog.game', $base + ['availability' => 'kosong']))))->toBe(['Alpha Tengah']);
    expect(catalogNames($this->actingAs($this->user)->get(route('catalog.game', $base + ['tag' => 'presale']))))->toBe(['Beta Murah']);
});

test('filter rentang harga memakai harga final IDR', function () {
    $price = fn (Product $p) => app(ShippingCalculatorService::class)->calculateItemShipping($p)['final_price_bersih'];
    $names = catalogNames($this->actingAs($this->user)->get(route('catalog.game', [
        'slug' => $this->game->slug,
        'min_price' => $price($this->mid),
        'max_price' => $price($this->mid),
    ])));

    expect($names)->toBe(['Alpha Tengah']);
});

test('pencarian menghormati sort dan filter ketersediaan', function () {
    $r = $this->actingAs($this->user)->get(route('search', ['q' => 'a', 'sort' => 'termahal', 'availability' => 'tersedia']));
    expect($r->viewData('results')->pluck('name')->all())->toBe(['Gamma Mahal', 'Beta Murah']);
});

test('nilai sort/harga tidak valid diabaikan tanpa error', function () {
    $this->actingAs($this->user)->get(route('catalog.game', ['slug' => $this->game->slug, 'sort' => 'x;drop', 'min_price' => 'abc', 'availability' => 'zzz']))->assertOk();
});
