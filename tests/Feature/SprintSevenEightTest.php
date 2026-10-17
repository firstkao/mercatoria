<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Game;
use App\Models\Order;
use App\Models\OrderItemSnapshot;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SprintSevenEightTest extends TestCase
{
    public function test_admin_can_update_shipping_defaults_and_ga4_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('office.settings.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('office.settings.edit'))
            ->assertOk()
            ->assertSee('Ongkir CN')
            ->assertSee('Measurement ID');

        $this->patch(route('office.settings.update'), [
            'ongkir_cn_btm_per_kg' => 32000,
            'ongkir_btm_jkt_per_kg' => 70000,
            'default_weight_gram' => 450,
            'ga4_measurement_id' => 'G-AB12CDEF34',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('32000', Setting::getValue('ongkir_cn_btm_per_kg'));
        $this->assertSame('70000', Setting::getValue('ongkir_btm_jkt_per_kg'));
        $this->assertSame('450', Setting::getValue('default_weight_gram'));
        $this->assertSame('G-AB12CDEF34', Setting::getValue('ga4_measurement_id'));
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id,
            'action' => 'settings_updated',
        ]);

        $product = $this->product('settings-weight');
        $product->update(['weight_gram' => null]);
        $variant = $product->variants()->create(['sku' => 'SETTINGS-VARIANT', 'price' => 10]);
        $this->assertSame(450, $product->fresh()->effectiveWeight());
        $this->assertSame(450, $variant->fresh()->effectiveWeight());
        $shipping = app(ShippingCalculatorService::class)->calculateItemShipping($product->fresh(), $variant->fresh());
        $this->assertSame(14400.0, $shipping['ongkir_cn_btm_idr']);
        $this->assertSame(31500.0, $shipping['ongkir_btm_jkt_idr']);
    }

    public function test_invalid_shipping_or_ga4_values_are_rejected_without_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::setValue('ongkir_cn_btm_per_kg', 25000);
        Setting::setValue('ongkir_btm_jkt_per_kg', 60000);
        Setting::setValue('default_weight_gram', 0);
        Setting::setValue('ga4_measurement_id', 'G-VALID1234');

        $this->actingAs($admin)->patch(route('office.settings.update'), [
            'ongkir_cn_btm_per_kg' => -1,
            'ongkir_btm_jkt_per_kg' => 60000,
            'default_weight_gram' => 0,
            'ga4_measurement_id' => 'not-a-measurement-id',
        ])->assertSessionHasErrors(['ongkir_cn_btm_per_kg', 'ga4_measurement_id']);

        $this->assertSame('25000', Setting::getValue('ongkir_cn_btm_per_kg'));
        $this->assertSame('G-VALID1234', Setting::getValue('ga4_measurement_id'));
    }

    public function test_ga4_is_public_only_and_core_ecommerce_events_are_emitted_without_pii(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::setValue('ga4_measurement_id', 'G-AB12CDEF34');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-AB12CDEF34')
            ->assertSee('view_home');
        $this->actingAs($admin)->get(route('office.settings.edit'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js');

        Setting::setValue('ga4_measurement_id', '');
        $this->get(route('home'))->assertDontSee('googletagmanager.com/gtag/js');
    }

    public function test_paid_order_purchase_event_is_emitted_once_for_the_customer_session(): void
    {
        Setting::setValue('ga4_measurement_id', 'G-AB12CDEF34');
        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'tgl_lahir' => '1995-01-01',
            'no_hp' => '628123456789',
        ]);
        $order = $user->orders()->create([
            'order_no' => 'MER-GA412-0001',
            'status' => OrderStatus::PEMBAYARAN_DITERIMA->value,
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp,
            'customer_address' => 'Jakarta',
            'subtotal' => 200000,
            'total' => 200000,
        ]);
        Payment::create([
            'order_id' => $order->id,
            'type' => 'full',
            'amount' => 200000,
            'method' => 'bca',
            'status' => 'verified',
            'is_locked' => false,
            'verified_at' => now(),
        ]);
        $item = $order->items()->create(['qty' => 1]);
        OrderItemSnapshot::create([
            'order_item_id' => $item->id,
            'product_name' => 'Snapshot GA4 Item',
            'product_slug' => 'snapshot-ga4-item',
            'product_sku' => 'GA4-ITEM-1',
            'final_price' => 200000,
            'qty' => 1,
            'subtotal' => 200000,
        ]);

        $firstResponse = $this->actingAs($user)->get(route('orders.show', $order));
        $firstResponse->assertOk()
            ->assertSee('purchase')
            ->assertSee('MER-GA412-0001')
            ->assertSee('GA4-ITEM-1');

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('"purchase"');
    }

    public function test_cart_checkout_and_payment_events_are_emitted_without_sending_customer_notes(): void
    {
        Setting::setValue('ga4_measurement_id', 'G-AB12CDEF34');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'tgl_lahir' => '1995-01-01',
            'no_hp' => '628123456789',
            'alamat_lengkap' => 'Jl. Merdeka',
            'kecamatan' => 'Kebayoran Baru',
        ]);
        $product = $this->product('ga4-cart');
        $cart = $user->carts()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'customer_note' => 'Catatan pribadi jangan dikirim ke GA4',
        ]);

        $this->actingAs($user)->from(route('products.direct', $product->slug))
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'qty' => 1,
            ])
            ->assertRedirect(route('products.direct', $product->slug));
        $this->get(route('products.direct', $product->slug))
            ->assertOk()
            ->assertSee('add_to_cart')
            ->assertSee('view_product');
        $this->get(route('cart.index'))->assertOk()->assertSee('view_cart');

        $this->get(route('cart.checkout', ['cart_ids' => [$cart->id]]))
            ->assertOk()
            ->assertSee('begin_checkout');

        $response = $this->post(route('cart.place-order'), [
            'cart_ids' => [$cart->id],
            'platform' => 'toco',
            'payment_scheme' => 'fp',
            'payment_method' => 'bca',
        ]);
        $response->assertRedirect()
            ->assertSessionHas('ga4_events', function (array $events): bool {
                return collect($events)->pluck('name')->all() === [
                    'add_payment_info',
                    'checkout_with_note',
                ] && ! str_contains(
                    json_encode($events, JSON_THROW_ON_ERROR),
                    'Catatan pribadi jangan dikirim ke GA4',
                );
            });

        $order = Order::query()->sole();
        $orderPage = $this->get(route('orders.show', $order));
        $orderPage->assertOk()
            ->assertSee('add_payment_info')
            ->assertSee('checkout_with_note');
    }

    public function test_legacy_order_import_copies_product_and_variant_images_to_snapshot_paths(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'is_migrated' => true,
            'no_hp' => '628123456789',
            'alamat_lengkap' => 'Jl. Merdeka',
            'kecamatan' => 'Kebayoran Baru',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12110',
        ]);
        $product = $this->product('legacy-images');
        Storage::disk('public')->put('products/legacy.jpg', 'product-image');
        Storage::disk('public')->put('variants/legacy.jpg', 'variant-image');
        $product->update(['main_image_path' => 'products/legacy.jpg']);
        $variant = $product->variants()->create([
            'sku' => 'LEGACY-IMAGE-VARIANT',
            'name' => 'Blue',
            'price' => 10,
            'image_path' => 'variants/legacy.jpg',
        ]);

        $response = $this->actingAs($admin)->post(route('office.orders.store'), [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 2,
            'unit_price' => 125000,
            'ordered_at' => '2025-09-03T10:30',
        ]);

        $response->assertRedirect();
        $snapshot = OrderItemSnapshot::query()->sole();
        $this->assertNotSame('products/legacy.jpg', $snapshot->product_image_path);
        $this->assertNotSame('variants/legacy.jpg', $snapshot->variant_image_path);
        Storage::disk('public')->assertExists($snapshot->product_image_path);
        Storage::disk('public')->assertExists($snapshot->variant_image_path);
        $this->assertSame(250000.0, (float) $snapshot->subtotal);
        $this->assertSame('2025-09-03', $snapshot->snapshot_at->toDateString());
    }

    private function product(string $slug): Product
    {
        $game = Game::create([
            'name' => "Game {$slug}",
            'slug' => "{$slug}-game",
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => "Product {$slug}",
            'slug' => $slug,
            'sku' => strtoupper($slug),
            'type' => 'single',
            'availability' => 'tersedia',
            'weight_gram' => 250,
            'is_active' => true,
            'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }
}
