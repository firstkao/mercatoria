<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Store;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Support\Facades\Cache;

function shippingService(): ShippingCalculatorService
{
    return app(ShippingCalculatorService::class);
}

function productWithStore(float $priceRmb, float $min, float $ongkir, int $weightGram = 0): Product
{
    $store = Store::factory()->create([
        'min_belanja_rmb' => $min,
        'ongkir_rmb' => $ongkir,
    ]);

    return Product::factory()->create([
        'price_rmb' => $priceRmb,
        'store_id' => $store->id,
        'weight_gram' => $weightGram,
    ]);
}

test('ongkir lokal cn proporsional sesuai contoh prd 47.5', function () {
    // rate 3.000 · min 100 · ongkir 7
    $p58 = productWithStore(58, 100, 7);
    $p80 = productWithStore(80, 100, 7);

    expect(shippingService()->calculateLocalCn($p58))->toEqual(12180.0)
        ->and(shippingService()->calculateLocalCn($p80))->toEqual(16800.0);
});

test('ongkir lokal cn gratis saat harga capai minimum', function () {
    $p100 = productWithStore(100, 100, 7);
    $p150 = productWithStore(150, 100, 7);

    expect(shippingService()->calculateLocalCn($p100))->toEqual(0.0)
        ->and(shippingService()->calculateLocalCn($p150))->toEqual(0.0);
});

test('ongkir lokal cn gratis saat min belanja nol', function () {
    $product = productWithStore(58, 0, 7);

    expect(shippingService()->calculateLocalCn($product))->toEqual(0.0);
});

test('ongkir lokal cn gratis saat ongkir store nol', function () {
    $product = productWithStore(58, 100, 0);

    expect(shippingService()->calculateLocalCn($product))->toEqual(0.0);
});

test('ongkir lokal cn gratis tanpa store', function () {
    $product = Product::factory()->create(['price_rmb' => 58, 'store_id' => null]);

    expect(shippingService()->calculateLocalCn($product))->toEqual(0.0);
});

test('ongkir lokal cn dihitung dari harga varian', function () {
    $product = productWithStore(200, 100, 7);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_rmb' => 58,
    ]);

    expect(shippingService()->calculateLocalCn($product, $variant))->toEqual(12180.0);
});

test('ongkir internasional sesuai contoh prd 47.6', function () {
    // CN-BTM 25.000/kg · BTM-JKT 60.000/kg
    expect(shippingService()->calculateCnBtm(50))->toEqual(1250.0)
        ->and(shippingService()->calculateBtmJkt(50))->toEqual(3000.0)
        ->and(shippingService()->calculateCnBtm(200))->toEqual(5000.0)
        ->and(shippingService()->calculateBtmJkt(200))->toEqual(12000.0)
        ->and(shippingService()->calculateCnBtm(500))->toEqual(12500.0)
        ->and(shippingService()->calculateBtmJkt(500))->toEqual(30000.0)
        ->and(shippingService()->calculateCnBtm(1000))->toEqual(25000.0)
        ->and(shippingService()->calculateBtmJkt(1000))->toEqual(60000.0)
        ->and(shippingService()->calculateCnBtm(2500))->toEqual(62500.0)
        ->and(shippingService()->calculateBtmJkt(2500))->toEqual(150000.0);
});

test('harga bersih item pakai profit 11 persen dan ceiling 5000', function () {
    // 58 RMB × 3.000 = 174.000 · lokal 12.180 · 50g = 1.250 + 3.000
    // modal 190.430 → +11% = 211.377,3 → CEILING ke 215.000
    $product = productWithStore(58, 100, 7, 50);

    $result = shippingService()->calculateItemShipping($product);

    expect($result['harga_produk_idr'])->toEqual(174000.0)
        ->and($result['ongkir_lokal_cn_idr'])->toEqual(12180.0)
        ->and($result['ongkir_cn_btm_idr'])->toEqual(1250.0)
        ->and($result['ongkir_btm_jkt_idr'])->toEqual(3000.0)
        ->and($result['ongkir_total_idr'])->toEqual(16430.0)
        ->and($result['base_price_bersih'])->toEqual(215000.0)
        ->and($result['final_price_bersih'])->toEqual(215000.0);
});

test('harga bersih item memotong diskon aktif', function () {
    $product = productWithStore(58, 100, 7, 50);
    $product->update([
        'discount_amount' => 15000,
        'discount_start' => now()->subDay(),
        'discount_end' => now()->addDay(),
    ]);

    $result = shippingService()->calculateItemShipping($product);

    expect($result['base_price_bersih'])->toEqual(215000.0)
        ->and($result['discount_amount'])->toEqual(15000.0)
        ->and($result['final_price_bersih'])->toEqual(200000.0);
});

test('diskon tidak berlaku di luar periode', function () {
    $product = productWithStore(58, 100, 7, 50);
    $product->update([
        'discount_amount' => 15000,
        'discount_start' => now()->subDays(10),
        'discount_end' => now()->subDays(5),
    ]);

    $result = shippingService()->calculateItemShipping($product);

    expect($result['discount_amount'])->toEqual(0.0)
        ->and($result['final_price_bersih'])->toEqual(215000.0);
});

test('item shipping memakai harga dan berat varian bila ada', function () {
    $product = productWithStore(200, 100, 7, 500);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_rmb' => 58,
        'weight_gram' => 50,
    ]);

    $result = shippingService()->calculateItemShipping($product, $variant);

    // varian 58 RMB & 50g → sama dengan kasus produk polos
    expect($result['price_rmb'])->toEqual(58.0)
        ->and($result['weight_gram'])->toEqual(50)
        ->and($result['base_price_bersih'])->toEqual(215000.0);
});

test('varian tanpa harga dan berat fallback ke produk', function () {
    $product = productWithStore(58, 100, 7, 50);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_rmb' => null,
        'weight_gram' => null,
    ]);

    $result = shippingService()->calculateItemShipping($product, $variant);

    expect($result['price_rmb'])->toEqual(58.0)
        ->and($result['weight_gram'])->toEqual(50)
        ->and($result['base_price_bersih'])->toEqual(215000.0);
});

test('checkout menggunakan setting dp dan fee qris yang aktif', function () {
    Setting::create(['key' => 'dp_minimum_percent', 'value' => '60', 'type' => 'int', 'group' => 'pricing']);
    Setting::create(['key' => 'qris_threshold', 'value' => '100000', 'type' => 'int', 'group' => 'pricing']);
    Setting::create(['key' => 'fee_qris_percent', 'value' => '0.03', 'type' => 'string', 'group' => 'pricing']);
    Cache::forget('app_settings_all');

    $result = shippingService()->calculateCheckoutTotal(
        [['final_price_bersih' => 200000, 'qty' => 1]],
        scheme: 'dp',
        platform: 'shopee',
        method: 'qris',
    );

    expect($result['fee_qris'])->toEqual(36.0)
        ->and($result['tagihan_web'])->toEqual(120036.0)
        ->and($result['estimasi_co_marketplace'])->toEqual(100000.0);
});
