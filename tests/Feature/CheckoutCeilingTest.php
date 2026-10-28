<?php

use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Services\Order\ShippingCalculatorService;

function itemShipping(Product $product): array
{
    return app(ShippingCalculatorService::class)->calculateItemShipping($product);
}

test('contoh prd 20.7 — 58 rmb 500g menjadi rp 255.000', function () {
    // modal 228.680 → +11% = 253.834,80 → CEILING → 255.000
    $store = Store::factory()->create(['min_belanja_rmb' => 100, 'ongkir_rmb' => 7]);
    $product = Product::factory()->create([
        'price_rmb' => 58,
        'weight_gram' => 500,
        'store_id' => $store->id,
    ]);

    $result = itemShipping($product);

    expect($result['base_price_bersih'])->toEqual(255000.0)
        ->and($result['final_price_bersih'])->toEqual(255000.0);
});

test('batas pembulatan — tepat kelipatan 5000 tidak dinaikkan', function () {
    Setting::create(['key' => 'profit_percent', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    // 55 × 3.000 = 165.000 + 1.000g (25.000 + 60.000) = modal 250.000 → tetap 250.000
    $product = Product::factory()->create([
        'price_rmb' => 55,
        'weight_gram' => 1000,
        'store_id' => null,
    ]);

    expect(itemShipping($product)['base_price_bersih'])->toEqual(250000.0);
});

test('batas pembulatan — 1 rupiah di atas kelipatan dinaikkan', function () {
    Setting::create(['key' => 'profit_percent', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    // 55 × 3.000 = 165.000 + 1.001g (25.025 + 60.060) = modal 250.085 → CEILING → 255.000
    $product = Product::factory()->create([
        'price_rmb' => 55,
        'weight_gram' => 1001,
        'store_id' => null,
    ]);

    expect(itemShipping($product)['base_price_bersih'])->toEqual(255000.0);
});

test('ceiling hanya sekali — jumlah checkout tidak dibulatkan ulang', function () {
    // 2 item sudah bulat: 255.000 + 85.000 = 340.000; koin 17.000 → 323.000
    $result = app(ShippingCalculatorService::class)->calculateCheckoutTotal(
        [['final_price_bersih' => 255000, 'qty' => 1], ['final_price_bersih' => 85000, 'qty' => 1]],
        17000,
        'fp',
        'toco',
        'bca'
    );

    expect($result['total_barang'])->toEqual(323000.0)
        ->and($result['total_barang'] % 5000)->not->toBe(0.0);
});

test('diskon produk dipotong setelah ceiling', function () {
    Setting::create(['key' => 'profit_percent', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    $product = Product::factory()->create([
        'price_rmb' => 55,
        'weight_gram' => 1000,
        'store_id' => null,
        'discount_amount' => 20000,
        'discount_start' => now()->subDay(),
        'discount_end' => now()->addDay(),
    ]);

    // base 250.000 - diskon 20.000 = 230.000 (tanpa pembulatan ulang)
    $result = itemShipping($product);

    expect($result['base_price_bersih'])->toEqual(250000.0)
        ->and($result['final_price_bersih'])->toEqual(230000.0);
});
