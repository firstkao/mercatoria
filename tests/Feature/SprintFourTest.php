<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Coin;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Order\OrderStatusService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SprintFourTest extends TestCase
{
    public function test_cart_requires_login_merges_products_and_only_allows_the_owner_to_edit(): void
    {
        $product = $this->createProduct('cart-single', 'single');
        $user = $this->createCustomer();
        $otherUser = $this->createCustomer('other-cart@example.com', '628123456788');

        $this->get(route('cart.index'))->assertRedirect(route('login'));

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 2,
        ])->assertRedirect();
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 3,
        ])->assertRedirect();

        $cart = Cart::query()->sole();
        $this->assertSame(5, $cart->qty);
        $this->patch(route('cart.update', $cart), [
            'qty' => 4,
            'customer_note' => 'Bungkus dengan aman',
        ])->assertRedirect(route('cart.index'));
        $this->assertSame('Bungkus dengan aman', $cart->fresh()->customer_note);

        $this->actingAs($otherUser)->patch(route('cart.update', $cart), ['qty' => 1])->assertNotFound();
    }

    public function test_variant_products_require_an_available_variant(): void
    {
        $product = $this->createProduct('cart-variant', 'variant');
        $variant = $product->variants()->create([
            'sku' => 'VARIANT-CART-1',
            'name' => 'Merah',
            'availability' => 'tersedia',
            'price' => 0,
        ]);
        $user = $this->createCustomer();

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ])->assertSessionHasErrors('variant_id');

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $variant->update(['availability' => 'kosong']);
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'qty' => 1,
        ])->assertSessionHasErrors('variant_id');
    }

    public function test_checkout_gates_verification_then_profile_and_creates_order_snapshots(): void
    {
        $product = $this->createProduct('checkout-product', 'single', [
            'main_image_path' => 'products/checkout.webp',
        ]);
        Storage::fake('public');
        Storage::disk('public')->put('products/checkout.webp', 'product-image');
        $user = $this->createCustomer();
        $cart = $user->carts()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'customer_note' => 'Bungkus aman',
        ]);
        $user->forceFill(['email_verified_at' => null])->save();

        $this->actingAs($user)->get(route('cart.checkout', ['cart_ids' => [$cart->id]]))->assertOk();
        $this->post(route('cart.place-order'), $this->checkoutPayload($cart))->assertRedirect(route('verification.notice'));

        $user->forceFill(['email_verified_at' => now()])->save();
        $this->post(route('cart.place-order'), $this->checkoutPayload($cart))->assertRedirect(route('profile.complete'));
        $this->assertDatabaseCount('orders', 0);

        $user->update(['tgl_lahir' => '1995-01-01']);
        $this->post(route('cart.place-order'), $this->checkoutPayload($cart))
            ->assertRedirect(route('orders.show', Order::query()->first()));

        $order = Order::query()->with('items.snapshot')->sole();
        $snapshot = $order->items->sole()->snapshot;
        $this->assertSame('single', $product->type);
        $this->assertSame($product->id, $snapshot->product_id_audit);
        $this->assertSame(2, $snapshot->qty);
        $this->assertSame('Product checkout-product: Bungkus aman', $order->customer_note);
        $this->assertGreaterThan(0, (float) $order->total);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        Storage::disk('public')->assertExists($snapshot->product_image_path);
    }

    public function test_checkout_applies_coins_or_voucher_and_rejects_combining_them(): void
    {
        $user = $this->createCustomer();
        $user->update(['tgl_lahir' => '1995-01-01']);
        $product = $this->createProduct('discounted-checkout');
        $coin = Coin::create(['user_id' => $user->id, 'balance' => 100_000]);
        $coinCart = $user->carts()->create(['product_id' => $product->id, 'qty' => 1]);

        $this->actingAs($user)->post(route('cart.place-order'), [
            ...$this->checkoutPayload($coinCart),
            'use_coins' => 1,
        ])->assertRedirect();

        $coinOrder = Order::query()->sole();
        $this->assertGreaterThan(0, (float) $coinOrder->coin_discount_amount);
        $this->assertLessThanOrEqual(
            (float) $coinOrder->subtotal * 0.05,
            (float) $coinOrder->coin_discount_amount,
        );
        $this->assertSame(100_000 - (int) $coinOrder->coin_discount_amount, (int) $coin->fresh()->balance);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'order_id' => $coinOrder->id,
            'amount' => -(int) $coinOrder->coin_discount_amount,
            'source' => 'used_at_checkout',
        ]);

        $voucher = Voucher::create([
            'code' => 'CHECKOUT10',
            'name' => 'Diskon checkout',
            'type' => 'nominal',
            'value' => 10_000,
            'min_purchase' => 0,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        $userVoucher = UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'status' => 'active',
        ]);
        $voucherCart = $user->carts()->create(['product_id' => $product->id, 'qty' => 1]);

        $this->post(route('cart.place-order'), [
            ...$this->checkoutPayload($voucherCart),
            'user_voucher_id' => $userVoucher->id,
        ])->assertRedirect();

        $voucherOrder = Order::query()->latest('id')->firstOrFail();
        $this->assertSame($userVoucher->id, $voucherOrder->user_voucher_id);
        $this->assertSame(10_000.0, (float) $voucherOrder->voucher_discount_amount);
        $this->assertDatabaseHas('user_vouchers', [
            'id' => $userVoucher->id,
            'status' => 'used',
            'order_id' => $voucherOrder->id,
        ]);

        $secondUserVoucher = UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'status' => 'active',
        ]);
        $combinedCart = $user->carts()->create(['product_id' => $product->id, 'qty' => 1]);
        $this->post(route('cart.place-order'), [
            ...$this->checkoutPayload($combinedCart),
            'use_coins' => 1,
            'user_voucher_id' => $secondUserVoucher->id,
        ])->assertSessionHasErrors('user_voucher_id');

        $this->assertSame(2, Order::query()->count());
        $this->assertDatabaseHas('user_vouchers', [
            'id' => $secondUserVoucher->id,
            'status' => 'active',
            'order_id' => null,
        ]);

        app(OrderStatusService::class)->changeStatus($coinOrder, OrderStatus::PEMBAYARAN_GAGAL);
        $this->assertSame(100_000, (int) $coin->fresh()->balance);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'order_id' => $coinOrder->id,
            'amount' => (int) $coinOrder->coin_discount_amount,
            'source' => 'checkout_release',
        ]);

        app(OrderStatusService::class)->changeStatus($voucherOrder, OrderStatus::PEMBAYARAN_GAGAL);
        $this->assertDatabaseHas('user_vouchers', [
            'id' => $userVoucher->id,
            'status' => 'active',
            'order_id' => null,
        ]);
    }

    public function test_hard_delete_preserves_order_snapshot_and_removes_original_media(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/delete-me.webp', 'original');
        Storage::disk('public')->put('products/variant.webp', 'variant');

        $admin = $this->createCustomer('admin@example.com', '628123456787', ['role' => 'admin']);
        $product = $this->createProduct('delete-product', 'variant', [
            'main_image_path' => 'products/delete-me.webp',
            'description_image_path' => 'products/variant.webp',
        ]);
        $variant = $product->variants()->create([
            'sku' => 'DELETE-VARIANT',
            'name' => 'Varian',
            'image_path' => 'products/variant.webp',
        ]);
        $orderItem = $this->createHistoricalOrderItem($product, $variant);
        $snapshot = $orderItem->snapshot;

        $this->actingAs($admin)->delete(route('office.products.destroy', $product))
            ->assertRedirect(route('office.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'product_id' => null]);
        $this->assertDatabaseHas('order_item_snapshots', [
            'id' => $snapshot->id,
            'product_id_audit' => $product->id,
            'product_name' => 'Product delete-product',
        ]);
        Storage::disk('public')->assertMissing('products/delete-me.webp');
        Storage::disk('public')->assertMissing('products/variant.webp');
    }

    public function test_admin_can_bulk_hard_delete_products(): void
    {
        $admin = $this->createCustomer('bulk-admin@example.com', '628123456786', ['role' => 'admin']);
        $first = $this->createProduct('bulk-one');
        $second = $this->createProduct('bulk-two');

        $this->actingAs($admin)->delete(route('office.products.bulk-destroy'), [
            'product_ids' => [$first->id, $second->id],
        ])->assertRedirect(route('office.products.index'))
            ->assertSessionHas('success', '2 produk dihapus permanen.');

        $this->assertDatabaseMissing('products', ['id' => $first->id]);
        $this->assertDatabaseMissing('products', ['id' => $second->id]);
    }

    private function createCustomer(
        string $email = 'customer@example.com',
        string $phone = '628123456789',
        array $attributes = [],
    ): User {
        return User::factory()->create([
            'email' => $email,
            'no_hp' => $phone,
            'alamat_lengkap' => 'Jl. Merdeka nomor 10',
            'kecamatan' => 'Kebayoran Baru',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12110',
            ...$attributes,
        ]);
    }

    private function createProduct(string $slug, string $type = 'single', array $attributes = []): Product
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
            'type' => $type,
            'availability' => 'tersedia',
            'weight_gram' => 250,
            'is_active' => true,
            'price_rmb' => 58,
            ...$attributes,
        ]);
        $product->games()->attach($game);

        return $product;
    }

    private function checkoutPayload(Cart $cart): array
    {
        return [
            'cart_ids' => [$cart->id],
            'platform' => 'toco',
            'payment_scheme' => 'fp',
            'payment_method' => 'bca',
        ];
    }

    private function createHistoricalOrderItem(Product $product, ?ProductVariant $variant = null)
    {
        $user = $this->createCustomer('history@example.com', '628123456785');
        $order = $user->orders()->create([
            'order_no' => 'MC-HISTORICAL-001',
            'status' => 'selesai',
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp,
            'customer_address' => 'Jl. Merdeka, Jakarta',
            'subtotal' => 100000,
            'total' => 100000,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'qty' => 1,
        ]);
        $item->snapshot()->create([
            'product_id_audit' => $product->id,
            'variant_id_audit' => $variant?->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_sku' => $product->sku,
            'product_image_path' => 'order-snapshots/old/product.jpg',
            'variant_image_path' => 'order-snapshots/old/variant.jpg',
        ]);

        return $item->load('snapshot');
    }
}
