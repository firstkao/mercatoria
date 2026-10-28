<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Cart\CheckoutService;
use App\Services\Order\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
