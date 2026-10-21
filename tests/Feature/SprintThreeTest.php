<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Order;
use App\Models\OrderItemSnapshot;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SprintThreeTest extends TestCase
{
    public function test_legacy_user_form_uses_the_migration_notice_and_warning_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('office.users.create'))
            ->assertOk()
            ->assertSee('Tambah User Baru')
            ->assertSee('Data Dasar')
            ->assertSee('Alamat Pengiriman')
            ->assertSee('Setelah simpan')
            ->assertSee('Simpan &amp; Lanjut Order', false);
    }

    public function test_admin_can_inject_a_legacy_customer_and_continue_to_order_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('office.users.store'), $this->legacyCustomerData());

        $user = User::query()->where('email', 'legacy@example.com')->firstOrFail();

        $response->assertRedirect("/office/orders/create?user_id={$user->id}");
        $this->assertSame('customer', $user->role);
        $this->assertTrue($user->isMigrated());
        $this->assertSame('mvp', Customer::query()->where('user_id', $user->id)->value('current_state'));
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->tgl_lahir);
        $this->assertSame('628123456789', $user->no_hp);
        $this->assertNotEmpty($user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Kebayoran Baru',
            'alamat_lengkap' => 'Jl. Contoh 10',
            'kode_pos' => '12110',
        ]);
        $this->get("/office/orders/create?user_id={$user->id}")
            ->assertOk()
            ->assertSee('Import Order Lama')
            ->assertSee('legacy@example.com');
    }

    public function test_admin_can_import_a_legacy_order_and_freeze_its_historical_price_and_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'is_migrated' => true,
            'no_hp' => '628123456789',
            'alamat_lengkap' => 'Jl. Contoh 10',
            'kecamatan' => 'Kebayoran Baru',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12110',
        ]);
        $product = $this->createProduct('legacy-order-product');

        $response = $this->actingAs($admin)->post(route('office.orders.store'), [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 125000,
            'ordered_at' => '2025-09-03T10:30',
        ]);

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $snapshot = OrderItemSnapshot::query()->firstOrFail();

        $response->assertRedirect(route('office.users.show', $user))
            ->assertSessionHas('success');
        $this->assertSame('selesai', $order->status);
        $this->assertSame(250000.0, (float) $order->total);
        $this->assertSame('2025-09-03', $order->created_at->toDateString());
        $this->assertSame(125000.0, (float) $snapshot->final_price);
        $this->assertSame(2, $snapshot->qty);
        $this->assertSame(250, $snapshot->weight_gram);
        $this->assertSame('2025-09-03', $snapshot->snapshot_at->toDateString());
    }

    public function test_legacy_order_import_requires_a_customer_phone_before_creating_the_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['is_migrated' => true, 'no_hp' => null]);
        $product = $this->createProduct('legacy-order-without-phone');

        $this->actingAs($admin)->post(route('office.orders.store'), [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 125000,
            'ordered_at' => '2025-09-03T10:30',
        ])
            ->assertSessionHasErrors([
                'user_id' => 'Customer belum memiliki nomor HP. Lengkapi data customer sebelum mengimpor order lama.',
            ]);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_legacy_order_import_rejects_regular_users_and_variants_from_other_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['is_migrated' => false]);
        $product = $this->createProduct('legacy-import-product');
        $otherProduct = $this->createProduct('other-legacy-product');
        $variant = $otherProduct->variants()->create(['sku' => 'LEGACY-OTHER-VARIANT', 'price' => 10]);

        $response = $this->actingAs($admin)->post(route('office.orders.store'), [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 1,
            'unit_price' => 100,
            'ordered_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors(['user_id', 'variant_id']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_legacy_customer_injection_requires_indonesian_phone_and_complete_address(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('office.users.store'), [
            ...$this->legacyCustomerData(),
            'no_hp' => '0712345',
            'kecamatan' => '',
        ]);

        $response->assertSessionHasErrors(['no_hp', 'kecamatan']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_only_admins_can_access_legacy_customer_tools(): void
    {
        $this->get(route('office.users.index'))->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('office.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_send_a_password_reset_link_and_the_action_is_logged(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['is_migrated' => true]);

        $response = $this->actingAs($admin)->post(route('office.users.force-reset-password', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id,
            'action' => 'force_password_reset',
            'model' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_admin_can_force_verify_email_and_the_action_is_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($admin)->post(route('office.users.force-verify-email', $user));

        $response->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id,
            'action' => 'force_verify_email',
            'model' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_order_gate_redirects_in_verification_then_profile_completion_order(): void
    {
        Route::middleware(['auth', 'ensure.verified', 'ensure.profile'])
            ->get('/dashboard/_test/order-gate', fn () => response('ready'));

        $user = User::factory()->unverified()->create(['tgl_lahir' => null]);

        $this->actingAs($user)->get('/dashboard/_test/order-gate')
            ->assertRedirect(route('verification.notice'));

        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)->get('/dashboard/_test/order-gate')
            ->assertRedirect(route('profile.complete'));

        $user->update(['tgl_lahir' => '1990-01-01']);

        $this->actingAs($user)->get('/dashboard/_test/order-gate')->assertOk();
    }

    public function test_dashboard_shows_the_email_verification_banner_for_unverified_customers(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Verifikasi email dulu biar bisa order');
    }

    public function test_incomplete_verified_profile_shows_order_gate_on_product_cart_and_checkout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'tgl_lahir' => null,
        ]);
        $product = $this->createProduct();
        $product->update(['type' => 'single']);
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $productResponse = $this->actingAs($user)->get(route('products.direct', $product->slug));
        $productResponse->assertOk()->assertSee('Lengkapi profil dulu');
        $this->assertMatchesRegularExpression(
            '/<button\b(?=[^>]*class="flex-1 rounded-lg bg-\[#4FC3F7\])(?=[^>]*\sdisabled(?:\s|=|>))[^>]*>/s',
            $productResponse->getContent(),
        );

        $cartResponse = $this->get(route('cart.index'));
        $cartResponse->assertOk()->assertSee('Lengkapi Profil →');
        $this->assertMatchesRegularExpression(
            '/<button\b(?=[^>]*class=["\']mt-4 w-full rounded-lg bg-\[#4FC3F7\])(?=[^>]*\sdisabled(?:\s|=|>))[^>]*>/s',
            $cartResponse->getContent(),
        );

        $checkoutResponse = $this->get(route('cart.checkout', ['cart_ids' => [$cart->id]]));
        $checkoutResponse->assertOk()
            ->assertSee('Buat Pesanan')
            ->assertSee('Lengkapi Profil →');
        $this->assertMatchesRegularExpression(
            '/<button\b(?=[^>]*class="w-full rounded-lg bg-\[#4FC3F7\])(?=[^>]*\sdisabled(?:\s|=|>))[^>]*>/s',
            $checkoutResponse->getContent(),
        );

        $user->update(['tgl_lahir' => '1990-01-01']);

        $response = $this->get(route('products.direct', $product->slug));
        $response->assertOk()->assertDontSee('Lengkapi Profil →');
        $this->assertDoesNotMatchRegularExpression(
            '/<button\b(?=[^>]*class="flex-1 rounded-lg bg-\[#4FC3F7\])(?=[^>]*\sdisabled(?:\s|=|>))[^>]*>/s',
            $response->getContent(),
        );
    }

    public function test_admin_product_form_previews_weight_based_shipping_and_requires_a_weight(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('office.products.create'))
            ->assertOk()
            ->assertSee('Berat produk default. Varian bisa override.')
            ->assertSee('Ongkir CN–BTM')
            ->assertSee('Ongkir BTM–JKT')
            ->assertSee('Preview diperbarui saat harga, berat, atau Store berubah.');

        $game = $this->createGame();
        $response = $this->actingAs($admin)->post(route('office.products.store'), [
            ...$this->productData($game),
            'weight_gram' => '',
        ]);

        $response->assertSessionHasErrors('weight_gram');
        $this->assertDatabaseMissing('products', ['slug' => 'weighted-product']);
    }

    public function test_admin_can_create_and_inline_update_a_variant_weight_with_product_fallback(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();

        $response = $this->actingAs($admin)->post(route('office.products.variants.store', $product), [
            'sku' => 'WEIGHT-VARIANT-1',
            'name' => 'Small',
            'price' => 12,
            'availability' => 'tersedia',
            'weight_gram' => '',
        ]);

        $variant = ProductVariant::query()->where('sku', 'WEIGHT-VARIANT-1')->firstOrFail();
        $response->assertRedirect(route('office.products.edit', $product));
        $this->assertNull($variant->weight_gram);
        $this->assertSame(250, $variant->effectiveWeight());

        $this->actingAs($admin)->patch(route('office.products.variants.weight.update', [$product, $variant]), [
            'weight_gram' => 375,
        ])->assertRedirect(route('office.products.edit', $product));

        $this->assertSame(375, $variant->fresh()->weight_gram);
    }

    public function test_variant_weight_updates_cannot_target_another_products_variant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $otherProduct = $this->createProduct('other-weighted-product');
        $variant = $otherProduct->variants()->create([
            'sku' => 'OTHER-VARIANT',
            'price' => 10,
            'weight_gram' => 100,
        ]);

        $this->actingAs($admin)
            ->patch(route('office.products.variants.weight.update', [$product, $variant]), ['weight_gram' => 900])
            ->assertNotFound();

        $this->assertSame(100, $variant->fresh()->weight_gram);
    }

    public function test_admin_can_bulk_update_selected_variant_weights_only_for_the_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $first = $product->variants()->create(['sku' => 'BULK-1', 'price' => 10, 'weight_gram' => 100]);
        $second = $product->variants()->create(['sku' => 'BULK-2', 'price' => 10, 'weight_gram' => null]);
        $otherProduct = $this->createProduct('other-bulk-product');
        $other = $otherProduct->variants()->create(['sku' => 'BULK-OTHER', 'price' => 10, 'weight_gram' => 150]);

        $response = $this->actingAs($admin)->patch(route('office.products.variants.weight.bulk', $product), [
            'selected_variant_ids' => [$first->id, $second->id],
            'weight_gram' => 300,
        ]);

        $response->assertRedirect(route('office.products.edit', $product));
        $this->assertSame(300, $first->fresh()->weight_gram);
        $this->assertSame(300, $second->fresh()->weight_gram);
        $this->assertSame(150, $other->fresh()->weight_gram);

        $this->actingAs($admin)->patch(route('office.products.variants.weight.bulk', $product), [
            'selected_variant_ids' => [$other->id],
            'weight_gram' => 500,
        ])->assertSessionHasErrors('selected_variant_ids.0');

        $this->assertSame(150, $other->fresh()->weight_gram);
    }

    private function legacyCustomerData(): array
    {
        return [
            'name' => 'Legacy Customer',
            'email' => 'legacy@example.com',
            'no_hp' => '628123456789',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Kebayoran Baru',
            'alamat_lengkap' => 'Jl. Contoh 10',
            'kode_pos' => '12110',
        ];
    }

    private function productData(Game $game): array
    {
        return [
            'name' => 'Weighted Product',
            'slug' => 'weighted-product',
            'sku' => 'WEIGHTED-001',
            'price_rmb' => 58,
            'weight_gram' => 250,
            'type' => 'variant',
            'availability' => 'tersedia',
            'is_active' => '1',
            'game_ids' => [$game->id],
        ];
    }

    private function createGame(string $slug = 'weight-test-game'): Game
    {
        return Game::create([
            'name' => 'Weight Test Game',
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function createProduct(string $slug = 'weighted-product'): Product
    {
        $game = $this->createGame("{$slug}-game");
        $product = Product::create([
            'name' => 'Weighted Product',
            'slug' => $slug,
            'sku' => strtoupper($slug),
            'type' => 'variant',
            'availability' => 'tersedia',
            'weight_gram' => 250,
            'is_active' => true,
            'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }
}
