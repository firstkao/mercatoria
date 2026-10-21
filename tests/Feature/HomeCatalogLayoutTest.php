<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class HomeCatalogLayoutTest extends TestCase
{
    public function test_home_renders_sections_in_wireframe_order_with_full_footer(): void
    {
        $game = Game::create(['name' => 'Honkai: Star Rail', 'slug' => 'hsr', 'is_active' => true]);
        Game::create(['name' => 'Arknights', 'slug' => 'arknights', 'is_active' => true]);
        $developer = Developer::create(['name' => 'miHoYo', 'slug' => 'mihoyo']);
        $game->developers()->attach($developer);
        $presale = Tag::create(['name' => 'PRESALE', 'slug' => 'presale', 'color' => '#FFC107']);
        foreach (range(1, 15) as $number) {
            $product = Product::create(['name' => "Merch {$number}", 'slug' => "merch-{$number}", 'sku' => "M-{$number}", 'price_rmb' => 10, 'is_active' => true]);
            $product->games()->attach($game);
            $product->tags()->attach($presale);
        }
        $offer = Product::create(['name' => 'Offer Item', 'slug' => 'offer-item', 'sku' => 'O-1', 'price_rmb' => 200, 'discount_amount' => 5000, 'is_active' => true]);
        $offer->games()->attach($game);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSeeInOrder(['Jelajahi Game Favoritmu', 'Arknights', 'Honkai: Star Rail', 'Penawaran Terbaik', 'Merch Baru', 'Produk Terpopuler']);
        $response->assertSee('[HONKAI: STAR RAIL]', false)->assertSee('PRESALE')->assertSee('DISKON');
        $response->assertSee('TOCO')->assertSee('SHOPEE')->assertSee('Tanya Jawab Umum')->assertSee('miHoYo');
        $this->assertCount(14, $response->viewData('newProducts'));
        $this->assertCount(1, $response->viewData('bestOffers'));
    }

    public function test_guest_barrier_redirects_humans_to_login_and_keeps_intended_url(): void
    {
        $game = Game::create(['name' => 'Gate Game', 'slug' => 'gate-game', 'is_active' => true]);

        $developer = Developer::create(['name' => 'Gate Dev', 'slug' => 'gate-dev']);

        foreach (['/catalog', '/search?q=x', '/gate-game', '/developer/gate-dev'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
        $this->assertSame(url('/developer/gate-dev'), session('url.intended'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/gate-game')->assertOk();
        $this->assertNotNull($game);
    }

    public function test_verified_ai_and_social_bots_bypass_the_guest_barrier_but_fake_search_bots_do_not(): void
    {
        $this->get('/catalog', ['User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0)'])->assertOk();
        $this->get('/catalog', ['User-Agent' => 'facebookexternalhit/1.1'])->assertOk();
        $this->get('/catalog', ['User-Agent' => 'Googlebot/2.1'])->assertRedirect(route('login'));
    }

    public function test_catalog_uses_six_column_grid_filters_and_square_pagination(): void
    {
        $game = Game::create(['name' => 'Wire Game', 'slug' => 'wire-game', 'is_active' => true]);
        foreach (range(1, 31) as $number) {
            Product::create(['name' => "Item {$number}", 'slug' => "item-{$number}", 'sku' => "I-{$number}", 'price_rmb' => 10, 'is_active' => true])
                ->games()->attach($game);
        }
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get('/wire-game')->assertOk();

        $response->assertSee('lg:grid-cols-6', false)
            ->assertSee('Kategori Game')->assertSee('Filter Harga')->assertSee('Terapkan')
            ->assertSee('Terakhir Dilihat')
            ->assertSee('Menampilkan 1–30 dari 31 hasil')->assertSee('Pengurutan standar')
            ->assertSee('aria-label="Halaman berikutnya"', false);
        $this->assertCount(30, $response->viewData('products'));
    }

    public function test_search_page_shows_toolbar_variant_pill_and_empty_state(): void
    {
        $game = Game::create(['name' => 'Search Game', 'slug' => 'search-game', 'is_active' => true]);
        $product = Product::create(['name' => 'Dream Series', 'slug' => 'dream', 'sku' => 'D-1', 'price_rmb' => 10, 'is_active' => true]);
        $product->games()->attach($game);
        $variant = $product->variants()->create(['sku' => 'D-1-T', 'name' => 'Tartaglia']);
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('catalog.search', ['q' => 'Tartaglia']))->assertOk()
            ->assertSee('Menampilkan semua 1 hasil')->assertSee('Semua Game', false)->assertSee('🎯 Varian: Tartaglia', false)
            ->assertSee('variant='.$variant->id, false)->assertSee('[SEARCH GAME]', false);

        $this->actingAs($user)->get(route('catalog.search', ['q' => 'KataKunciTypo']))->assertOk()
            ->assertSee('Tidak ada produk atau varian yang cocok dengan "KataKunciTypo"', false)
            ->assertSee('Tips Pencarian')->assertSee('Reset Filter')->assertSee('Jelajahi Semua Game');
    }
}
