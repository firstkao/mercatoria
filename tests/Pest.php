<?php

use App\Models\Product;
use App\Models\Store;
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
