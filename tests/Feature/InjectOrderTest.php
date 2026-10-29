<?php

use App\Models\AdminLog;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserNotification;

function injectFixture(): array
{
    $game = Game::factory()->create();
    $product = Product::factory()->create([
        'game_id' => $game->id,
        'name' => 'Produk Lama',
        'price_rmb' => 58,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Varian Lama',
        'sku' => 'VAR-LAMA-1',
    ]);

    return [$product, $variant];
}

test('admin inject order lama dengan harga snapshot manual', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    [$product, $variant] = injectFixture();

    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => $customer->id,
        'status' => 'selesai',
        'items' => [
            ['product_id' => $product->id, 'variant_id' => $variant->id, 'qty' => 2, 'price' => 99999],
        ],
        'customer_note' => 'Order lama migrasi',
    ])->assertRedirect();

    $order = Order::where('user_id', $customer->id)->firstOrFail();
    $snapshot = $order->items->first()->snapshot;

    expect($order->status)->toBe('selesai')
        ->and($order->customer_note)->toBe('Order lama migrasi')
        // Harga snapshot = harga yang diketik admin, BUKAN hasil kalkulasi
        ->and((float) $snapshot->final_price)->toEqual(99999.0)
        ->and((float) $snapshot->subtotal)->toEqual(199998.0)
        ->and($snapshot->product_name)->toBe('Produk Lama')
        ->and($snapshot->variant_name)->toBe('Varian Lama');
});

test('status inject bebas langsung ke tahap mana pun', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    [$product] = injectFixture();

    foreach (['selesai', 'sampai_wh_indonesia', 'menunggu_denda', 'hangus_permanen'] as $status) {
        $this->actingAs($admin)->post('/office/orders/inject', [
            'user_id' => $customer->id,
            'status' => $status,
            'items' => [['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 10000]],
        ]);
    }

    expect(Order::where('user_id', $customer->id)->pluck('status')->all())
        ->toContain('selesai', 'sampai_wh_indonesia', 'menunggu_denda', 'hangus_permanen');
});

test('order inject tidak memicu efek samping — koin, voucher, notif, tier', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    Coin::factory()->create(['user_id' => $customer->id, 'balance' => 500]);
    [$product] = injectFixture();

    // Inject langsung ke Selesai — status yang normalnya memicu kredit koin & tier
    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => $customer->id,
        'status' => 'selesai',
        'items' => [['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 255000]],
    ]);

    // GUARD (keputusan user): order inject = catatan historis, bukan transaksi baru
    expect(CoinTransaction::count())->toBe(0)
        ->and(Coin::where('user_id', $customer->id)->value('balance'))->toBe(500)
        ->and(UserNotification::count())->toBe(0)
        ->and(Customer::where('user_id', $customer->id)->exists())->toBeFalse();
});

test('order inject tidak butuh bukti bayar', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    [$product] = injectFixture();

    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => $customer->id,
        'status' => 'pembayaran_diterima',
        'items' => [['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 50000]],
    ]);

    $order = Order::firstOrFail();
    expect($order->payments()->count())->toBe(0)
        ->and($order->status)->toBe('pembayaran_diterima');
});

test('inject tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    [$product] = injectFixture();

    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => $customer->id,
        'status' => 'selesai',
        'items' => [['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 50000]],
    ]);

    expect(AdminLog::where('action', 'create')->where('model', 'Order')->exists())->toBeTrue();
});

test('inject ditolak untuk non-admin', function () {
    $user = User::factory()->create();
    [$product] = injectFixture();

    $this->actingAs($user)->post('/office/orders/inject', [
        'user_id' => $user->id,
        'status' => 'selesai',
        'items' => [['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 1]],
    ])->assertForbidden();

    expect(Order::count())->toBe(0);
});

test('form inject menampilkan shortcut tambah user dan produk', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)->get('/office/orders/inject')->getContent();

    expect($html)->toContain(route('office.users.create'))
        ->toContain(route('office.products.create'));
});

test('validasi inject menolak data tidak sah', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/orders/inject', [
        'user_id' => 999999,
        'status' => 'status-ngawur',
        'items' => [],
    ])->assertSessionHasErrors(['user_id', 'status', 'items']);

    expect(Order::count())->toBe(0);
});
