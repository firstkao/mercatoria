<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class StoreAndCatalogTest extends TestCase
{
    public function test_store_management_requires_an_authenticated_admin(): void
    {
        $this->get(route('office.stores.index'))
            ->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('office.stores.index'))
            ->assertForbidden();
    }

    public function test_admin_can_search_stores_and_see_product_and_game_usage(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create([
            'name' => 'Official Example',
            'min_belanja_rmb' => 100,
            'ongkir_rmb' => 7,
        ]);
        $game = $this->createGame('Example Game', 'example-game', $store);
        $this->createProduct('Example Merchandise', 'example-merch', 'SKU-001', $game, store: $store);
        $secondGame = $this->createGame('Second Example Game', 'second-example-game', $store);
        $this->createProduct('Inherited Merchandise', 'inherited-merch', 'SKU-002', $secondGame);
        Store::create(['name' => 'Other Store', 'min_belanja_rmb' => 0, 'ongkir_rmb' => 0]);

        $response = $this->actingAs($admin)
            ->get(route('office.stores.index', ['q' => 'Official']));

        $response->assertOk()
            ->assertSee('Official Example')
            ->assertSee('2 produk')
            ->assertSee('2 game')
            ->assertSee('Lihat Produk')
            ->assertSee('Hapus')
            ->assertSee('delete-store-'.$store->id)
            ->assertSee('Total: 1 store')
            ->assertDontSee('Other Store');
    }

    public function test_admin_can_create_a_store_with_non_negative_shipping_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('office.stores.store'), [
            'name' => 'Free Shipping Tier',
            'min_belanja_rmb' => 0,
            'ongkir_rmb' => 0,
        ]);

        $store = Store::query()->where('name', 'Free Shipping Tier')->firstOrFail();

        $response->assertRedirect(route('office.stores.edit', $store))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'min_belanja_rmb' => 0,
            'ongkir_rmb' => 0,
        ]);
    }

    public function test_store_form_displays_calculation_examples_for_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('office.stores.create'))
            ->assertOk()
            ->assertSee('Preview Perhitungan')
            ->assertSee('Produk 58 RMB')
            ->assertSee('Produk 150 RMB');
    }

    public function test_store_validation_rejects_missing_names_and_negative_amounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('office.stores.store'), [
            'name' => '',
            'min_belanja_rmb' => -1,
            'ongkir_rmb' => -2,
        ]);

        $response->assertSessionHasErrors(['name', 'min_belanja_rmb', 'ongkir_rmb']);
        $this->assertDatabaseCount('stores', 0);
    }

    public function test_store_names_must_be_unique_but_the_current_name_can_be_kept_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create([
            'name' => 'Existing Store',
            'min_belanja_rmb' => 100,
            'ongkir_rmb' => 7,
        ]);

        $this->actingAs($admin)->post(route('office.stores.store'), [
            'name' => ' Existing Store ',
            'min_belanja_rmb' => 100,
            'ongkir_rmb' => 7,
        ])->assertSessionHasErrors(['name']);

        $this->put(route('office.stores.update', $store), [
            'name' => 'Existing Store',
            'min_belanja_rmb' => 120,
            'ongkir_rmb' => 8,
        ])->assertRedirect(route('office.stores.edit', $store))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('stores', 1);
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'min_belanja_rmb' => 120]);
    }

    public function test_admin_can_update_a_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create([
            'name' => 'Old Name',
            'min_belanja_rmb' => 100,
            'ongkir_rmb' => 7,
        ]);

        $response = $this->actingAs($admin)->put(route('office.stores.update', $store), [
            'name' => 'New Name',
            'min_belanja_rmb' => 150,
            'ongkir_rmb' => 0,
        ]);

        $response->assertRedirect(route('office.stores.edit', $store));
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'New Name',
            'min_belanja_rmb' => 150,
            'ongkir_rmb' => 0,
        ]);
    }

    public function test_admin_can_set_a_default_store_for_a_game(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create(['name' => 'Game Default', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);

        $response = $this->actingAs($admin)->post(route('office.games.store'), [
            'name' => 'Store Selector Game',
            'slug' => 'store-selector-game',
            'default_store_id' => $store->id,
            'is_active' => '1',
        ]);

        $game = Game::query()->where('slug', 'store-selector-game')->firstOrFail();

        $response->assertRedirect(route('office.games.edit', $game));
        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'default_store_id' => $store->id,
        ]);
    }

    public function test_game_form_offers_stores_for_the_default_store_selector(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Store::create(['name' => 'Game Form Store', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);

        $this->actingAs($admin)
            ->get(route('office.games.create'))
            ->assertOk()
            ->assertSee('Default Store')
            ->assertSee('default-store-search')
            ->assertSee('Game Form Store')
            ->assertSee('+ Store Baru');
    }

    public function test_product_can_select_a_store_or_use_the_game_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $defaultStore = Store::create(['name' => 'Game Default', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
        $overrideStore = Store::create(['name' => 'Product Override', 'min_belanja_rmb' => 150, 'ongkir_rmb' => 10]);
        $game = $this->createGame('Selector Game', 'selector-game', $defaultStore);

        $response = $this->actingAs($admin)->post(route('office.products.store'), [
            'name' => 'Selector Product',
            'slug' => 'selector-product',
            'sku' => 'SEL-001',
            'price_rmb' => 58,
            'weight_gram' => 250,
            'type' => 'single',
            'availability' => 'tersedia',
            'is_active' => '1',
            'game_ids' => [$game->id],
            'store_id' => $overrideStore->id,
        ]);

        $product = Product::query()->where('slug', 'selector-product')->firstOrFail();

        $response->assertRedirect(route('office.products.edit', $product));
        $this->assertSame($overrideStore->id, $product->fresh()->effectiveStore()->id);
    }

    public function test_product_form_shows_game_fallback_and_store_override_options(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create(['name' => 'Product Form Store', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
        $this->createGame('Product Form Game', 'product-form-game', $store);

        $response = $this->actingAs($admin)
            ->get(route('office.products.create'))
            ->assertOk()
            ->assertSee('— Pakai default game —')
            ->assertSee('Product Form Store')
            ->assertSee('product-store-search')
            ->assertSee('Default game:')
            ->assertSee('Total setelah pembulatan')
            ->assertSee('+ Store Baru');
        $this->assertMatchesRegularExpression(
            '/<aside[^>]*>.*<h2[^>]*>Store<\/h2>.*<h2[^>]*>Preview Perhitungan Harga<\/h2>.*<\/aside>/s',
            $response->getContent(),
        );
    }

    public function test_admin_sidebar_can_collapse_and_highlights_the_current_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('office.stores.index'))
            ->assertOk()
            ->assertSee('Ciutkan navigasi admin')
            ->assertSee('Stores (Tier Ongkir)')
            ->assertSee('bg-primary-600');
    }

    public function test_store_product_filter_includes_direct_and_game_default_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create(['name' => 'Filtered Store', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
        $directGame = $this->createGame('Direct Store Game', 'direct-store-game');
        $this->createProduct('Direct Store Product', 'direct-store-product', 'DIR-001', $directGame, store: $store);
        $inheritedGame = $this->createGame('Inherited Store Game', 'inherited-store-game', $store);
        $this->createProduct('Inherited Store Product', 'inherited-store-product', 'INH-001', $inheritedGame);

        $response = $this->actingAs($admin)->get(route('office.products.index', ['store_id' => $store->id]));

        $response->assertOk()
            ->assertSee('Direct Store Product')
            ->assertSee('Inherited Store Product');
    }

    public function test_product_without_store_override_uses_the_game_default_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $defaultStore = Store::create(['name' => 'Fallback Store', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
        $game = $this->createGame('Fallback Game', 'fallback-game', $defaultStore);

        $response = $this->actingAs($admin)->post(route('office.products.store'), [
            'name' => 'Fallback Product',
            'slug' => 'fallback-product',
            'sku' => 'FALL-001',
            'price_rmb' => 58,
            'weight_gram' => 250,
            'type' => 'single',
            'availability' => 'tersedia',
            'is_active' => '1',
            'game_ids' => [$game->id],
            'store_id' => '',
        ]);

        $product = Product::query()->where('slug', 'fallback-product')->firstOrFail()->load('games.defaultStore');

        $response->assertRedirect(route('office.products.edit', $product));
        $this->assertSame($defaultStore->id, $product->effectiveStore()->id);
    }

    public function test_deleting_a_store_clears_game_and_product_store_references(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::create([
            'name' => 'Used Store',
            'min_belanja_rmb' => 100,
            'ongkir_rmb' => 7,
        ]);
        $game = $this->createGame('Used Game', 'used-game', $store);
        $product = $this->createProduct('Used Product', 'used-product', 'SKU-USED', $game, store: $store);

        $response = $this->actingAs($admin)->delete(route('office.stores.destroy', $store));

        $response->assertRedirect(route('office.stores.index'));
        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
        $this->assertDatabaseHas('games', ['id' => $game->id, 'default_store_id' => null]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'store_id' => null]);
    }

    public function test_public_catalog_shows_active_products_but_hides_inactive_products(): void
    {
        $game = $this->createGame('Fantasy Game', 'fantasy-game');
        $this->createProduct('Available Figure', 'available-figure', 'FIG-001', $game);
        $this->createProduct('Hidden Figure', 'hidden-figure', 'FIG-002', $game, active: false);

        $response = $this->get(route('catalog.index'));

        $response->assertOk()
            ->assertSee('Available Figure')
            ->assertDontSee('Hidden Figure')
            ->assertSee('Fantasy Game');
    }

    public function test_catalog_search_matches_product_names_and_skus(): void
    {
        $game = $this->createGame('Search Game', 'search-game');
        $this->createProduct('Special Charm', 'special-charm', 'SKU-SEARCH-42', $game);

        $response = $this->get(route('catalog.search', ['q' => 'SKU-SEARCH-42']));

        $response->assertOk()->assertSee('Special Charm');
    }

    public function test_catalog_filters_by_tag_category_and_availability(): void
    {
        $game = $this->createGame('Filter Game', 'filter-game');
        $available = $this->createProduct('Limited Figure', 'limited-figure', 'FIG-LIMITED', $game);
        $available->update(['availability' => 'tersedia']);
        $category = MerchCategory::create(['name' => 'Figure', 'slug' => 'figure']);
        $tag = Tag::create(['name' => 'Limited', 'slug' => 'limited', 'color' => '#E53935']);
        $available->merchCategories()->attach($category);
        $available->tags()->attach($tag);
        $this->createProduct('Regular Figure', 'regular-figure', 'FIG-REGULAR', $game);

        $response = $this->get(route('games.show', $game->slug).'?tag=limited&category=figure&availability=tersedia');

        $response->assertOk()
            ->assertSee('Limited Figure')
            ->assertDontSee('Regular Figure');
    }

    public function test_catalog_price_sort_and_max_filter_use_display_price_after_discount(): void
    {
        $game = $this->createGame('Price Game', 'price-game');
        $discounted = $this->createProduct('Discounted Product', 'discounted-product', 'PRICE-001', $game);
        $discounted->update(['price_rmb' => 200, 'discount_amount' => 1_000_000]);
        $regular = $this->createProduct('Regular Price Product', 'regular-price-product', 'PRICE-002', $game);
        $regular->update(['price_rmb' => 20]);
        $free = $this->createProduct('Free Price Product', 'free-price-product', 'PRICE-003', $game);
        $free->update(['price_rmb' => 0]);

        $response = $this->get(route('catalog.index').'?sort=price_asc&max_price=0');

        $response->assertOk()
            ->assertViewHas('filters.max_price')
            ->assertSee('Discounted Product');
        $this->assertSame(
            [$free->id, $discounted->id],
            $response->viewData('products')->pluck('id')->all(),
        );
    }

    public function test_product_detail_requires_the_product_to_belong_to_the_requested_game(): void
    {
        $firstGame = $this->createGame('First Game', 'first-game');
        $secondGame = $this->createGame('Second Game', 'second-game');
        $product = $this->createProduct('First Game Product', 'first-game-product', 'GAME-001', $firstGame);

        $this->get(route('games.products.show', [$secondGame->slug, $product->slug]))
            ->assertNotFound();
    }

    public function test_developer_page_lists_products_from_its_active_games(): void
    {
        $developer = Developer::create(['name' => 'Example Developer', 'slug' => 'example-developer']);
        $game = $this->createGame('Developer Game', 'developer-game');
        $game->developers()->attach($developer);
        $this->createProduct('Developer Product', 'developer-product', 'DEV-001', $game);

        $response = $this->get(route('developers.show', $developer->slug));

        $response->assertOk()
            ->assertSee('Example Developer')
            ->assertSee('Developer Product');
    }

    private function createGame(string $name, string $slug, ?Store $store = null): Game
    {
        return Game::create([
            'name' => $name,
            'slug' => $slug,
            'default_store_id' => $store?->id,
            'is_active' => true,
        ]);
    }

    private function createProduct(
        string $name,
        string $slug,
        string $sku,
        Game $game,
        ?Store $store = null,
        bool $active = true,
    ): Product {
        $product = Product::create([
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'store_id' => $store?->id,
            'type' => 'single',
            'availability' => 'tersedia',
            'is_active' => $active,
            'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }
}
