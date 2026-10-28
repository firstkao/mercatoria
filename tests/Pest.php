<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SplitBox;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\SplitBoxSlot;
use App\Models\Store;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Cart\CheckoutService;
use App\Services\Order\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Produk dengan harga tayang final Rp 255.000 (contoh PRD 20.7: 58 RMB, 500g, store 100/7).
 */
function productFinal255(): Product
{
    $store = Store::factory()->create(['min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);

    return Product::factory()->create([
        'price_rmb' => 58,
        'weight_gram' => 500,
        'store_id' => $store->id,
    ]);
}

/**
 * Produk dengan harga tayang final Rp 85.000 (modal 75.000 → +11% → CEILING).
 */
function productFinal85(): Product
{
    return Product::factory()->create([
        'price_rmb' => 25,
        'weight_gram' => 0,
        'store_id' => null,
    ]);
}

/**
 * Payload default POST /register.
 */
function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'tgl_lahir' => '1998-08-15',
        'no_hp' => '081234567890',
        'email' => 'budi@example.com',
        'provinsi' => 'DKI Jakarta',
        'kota' => 'Jakarta Selatan',
        'kecamatan' => 'Kebayoran Baru',
        'alamat' => 'Jl. Melati No. 10, RT 01 / RW 02',
        'kode_pos' => '12120',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'agree_tnc' => '1',
        'agree_privacy' => '1',
    ], $overrides);
}

/**
 * Payload default POST /checkout (harga selalu dihitung ulang di server).
 */
function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'platform' => 'toco',
        'scheme' => 'fp',
        'payment_method' => 'bca',
        'discount_type' => 'none',
        'customer_note' => 'bubble wrap extra',
    ], $overrides);
}

/**
 * Buat order nyata via CheckoutService (produk final Rp 255.000).
 *
 * @return array{0: User, 1: Order}
 */
function placeTestOrder(?User $user = null): array
{
    $user ??= User::factory()->create();

    $cart = Cart::factory()->create([
        'user_id' => $user->id,
        'product_id' => productFinal255()->id,
        'qty' => 1,
    ]);

    $order = app(CheckoutService::class)->placeOrder(
        $user,
        [$cart->id],
        [
            'platform' => 'toco',
            'scheme' => 'fp',
            'payment_method' => 'bca',
            'discount_type' => 'none',
            'customer_note' => null,
        ]
    );

    return [$user, $order];
}

/**
 * Order dengan diskon koin (produk final Rp 255.000; maks 5% = 12.750).
 *
 * @return array{0: User, 1: Order}
 */
function placeOrderUsingCoin(int $coinAmount = 12750): array
{
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => $coinAmount]);

    $cart = Cart::factory()->create([
        'user_id' => $user->id,
        'product_id' => productFinal255()->id,
        'qty' => 1,
    ]);

    $order = app(CheckoutService::class)->placeOrder(
        $user,
        [$cart->id],
        [
            'platform' => 'toco',
            'scheme' => 'fp',
            'payment_method' => 'bca',
            'discount_type' => 'coin',
            'coin_amount' => $coinAmount,
            'customer_note' => null,
        ]
    );

    return [$user, $order];
}

/**
 * Order dengan diskon voucher (nominal 25.000).
 *
 * @return array{0: User, 1: Order, 2: UserVoucher}
 */
function placeOrderUsingVoucher(): array
{
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'min_belanja' => 100000, 'is_active' => true]);
    $userVoucher = UserVoucher::factory()->create([
        'user_id' => $user->id,
        'voucher_id' => $voucher->id,
        'status' => 'active',
    ]);

    $cart = Cart::factory()->create([
        'user_id' => $user->id,
        'product_id' => productFinal255()->id,
        'qty' => 1,
    ]);

    $order = app(CheckoutService::class)->placeOrder(
        $user,
        [$cart->id],
        [
            'platform' => 'toco',
            'scheme' => 'fp',
            'payment_method' => 'bca',
            'discount_type' => 'voucher',
            'user_voucher_id' => $userVoucher->id,
            'customer_note' => null,
        ]
    );

    return [$user, $order, $userVoucher];
}

/**
 * Majukan order sampai status Selesai lewat jalur resmi.
 */
function advanceToSelesai(Order $order): void
{
    $statuses = app(OrderStatusService::class);

    foreach ([
        'ditahan',
        'pembayaran_diterima',
        'sedang_diproses',
        'sampai_wh_china',
        'dikirim_ke_indonesia',
        'bea_cukai',
        'sampai_wh_indonesia',
        'menunggu_pelunasan_pengambilan',
        'sedang_dikemas',
        'selesai',
    ] as $status) {
        $statuses->changeStatus($order, $status, 'admin');
    }
}

/**
 * Produk Split Box: 1 hot_1 + 2 normal, harga/berat sama (PRD 49.5).
 *
 * @return array{user: User, product: Product, splitBox: SplitBox, hot: SplitBoxSlot, normalA: SplitBoxSlot, normalB: SplitBoxSlot, batch: SplitBoxBatch}
 */
function splitBoxFixture(int $stockPerSlot = 1): array
{
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $store = Store::factory()->create(['min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);

    $product = Product::factory()->create([
        'game_id' => $game->id,
        'store_id' => $store->id,
        'type' => 'variant',
        'is_split_box' => true,
        'price_rmb' => 58,
        'weight_gram' => 50,
    ]);

    $splitBox = SplitBox::create([
        'product_id' => $product->id,
        'max_auto_batches' => 3,
        'auto_open_batch' => true,
    ]);

    $makeSlot = function (string $name, string $role, int $sort) use ($product, $splitBox): SplitBoxSlot {
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => $name,
            'price_rmb' => 58,
            'weight_gram' => 50,
        ]);

        return SplitBoxSlot::create([
            'split_box_id' => $splitBox->id,
            'variant_id' => $variant->id,
            'split_role' => $role,
            'qty_per_box' => 1,
            'sort_order' => $sort,
        ]);
    };

    $hot = $makeSlot('Hot', 'hot_1', 0);
    $normalA = $makeSlot('Normal A', 'normal', 1);
    $normalB = $makeSlot('Normal B', 'normal', 2);

    $batch = SplitBoxBatch::create([
        'split_box_id' => $splitBox->id,
        'batch_no' => 1,
        'status' => 'open',
    ]);

    foreach ([$hot, $normalA, $normalB] as $slot) {
        SplitBoxBatchSlot::create([
            'batch_id' => $batch->id,
            'slot_id' => $slot->id,
            'qty_total' => $stockPerSlot,
            'qty_taken' => 0,
        ]);
    }

    return compact('user', 'product', 'splitBox', 'hot', 'normalA', 'normalB', 'batch');
}

/**
 * Buat cart bundle Split Box + placeOrder (qty per slot 1).
 *
 * @param  array<int, SplitBoxSlot>  $slots
 * @param  array<string, mixed>  $extra  data checkout tambahan (mis. diskon koin/voucher)
 * @return array{0: User, 1: Order, 2: string}
 */
function placeSplitBoxOrder(User $user, Product $product, array $slots, int $qty = 1, array $extra = []): array
{
    $bundleId = (string) Str::uuid();
    $cartIds = [];

    foreach ($slots as $slot) {
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $slot->variant_id,
            'qty' => $qty,
            'bundle_group_id' => $bundleId,
        ]);
        $cartIds[] = $cart->id;
    }

    $order = app(CheckoutService::class)->placeOrder($user, $cartIds, array_merge([
        'platform' => 'toco',
        'scheme' => 'fp',
        'payment_method' => 'bca',
        'discount_type' => 'none',
        'customer_note' => null,
    ], $extra));

    return [$user, $order, $bundleId];
}
