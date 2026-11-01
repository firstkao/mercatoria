<?php

use App\Services\Order\ShippingCalculatorService;

function checkoutService(): ShippingCalculatorService
{
    return app(ShippingCalculatorService::class);
}

test('contoh 1 prd 20.6 — koin 5 persen total barang tanpa pembulatan', function () {
    // item 255.000 + 85.000 = 340.000; koin 5% = 17.000; total = 323.000
    $result = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 255000, 'qty' => 1], ['final_price_bersih' => 85000, 'qty' => 1]],
        17000,
        'fp',
        'toco',
        'bca'
    );

    expect($result['subtotal'])->toEqual(340000.0)
        ->and($result['discount'])->toEqual(17000.0)
        ->and($result['total_barang'])->toEqual(323000.0)
        ->and($result['fee_qris'])->toEqual(0.0)
        ->and($result['tagihan_web'])->toEqual(323000.0)
        ->and($result['fee_co_marketplace'])->toEqual(1);
});

test('contoh 1 prd 20.6 — dp 50 persen toco dan shopee', function () {
    $items = [['final_price_bersih' => 255000, 'qty' => 1], ['final_price_bersih' => 85000, 'qty' => 1]];

    $toco = checkoutService()->calculateCheckoutTotal($items, 17000, 'dp', 'toco', 'bca');
    $shopee = checkoutService()->calculateCheckoutTotal($items, 17000, 'dp', 'shopee', 'bca');

    // DP web = 50% × 323.000 = 161.500 (bukan kelipatan 5.000 → tanpa CEILING)
    expect($toco['tagihan_web'])->toEqual(161500.0)
        ->and($toco['estimasi_co_marketplace'])->toEqual(161500.0)
        ->and($shopee['tagihan_web'])->toEqual(161500.0)
        ->and($shopee['estimasi_co_marketplace'])->toEqual(201875.0);
});

test('contoh 2 prd 20.6 — dp shopee pelunasan 25 persen', function () {
    $result = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 200000, 'qty' => 1]],
        0,
        'dp',
        'shopee',
        'bca'
    );

    expect($result['tagihan_web'])->toEqual(100000.0)
        ->and($result['estimasi_co_marketplace'])->toEqual(125000.0);
});

test('fee qris hanya di atas threshold 500 ribu', function () {
    $pas = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 500000, 'qty' => 1]],
        0,
        'fp',
        'toco',
        'qris'
    );
    $bawah = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 499999, 'qty' => 1]],
        0,
        'fp',
        'toco',
        'qris'
    );
    $nonQris = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 500000, 'qty' => 1]],
        0,
        'fp',
        'toco',
        'bca'
    );

    // 0,03% × 500.000 = 150
    expect($pas['fee_qris'])->toEqual(150.0)
        ->and($pas['tagihan_web'])->toEqual(500150.0)
        ->and($bawah['fee_qris'])->toEqual(0.0)
        ->and($nonQris['fee_qris'])->toEqual(0.0);
});

test('fee qris juga berlaku untuk dp', function () {
    $result = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 1000000, 'qty' => 1]],
        0,
        'dp',
        'toco',
        'qris'
    );

    // PRD 21: fee dari nominal web DP 500.000 (>= threshold) = 150
    expect($result['fee_qris'])->toEqual(150.0)
        ->and($result['tagihan_web'])->toEqual(500150.0);
});

test('fee marketplace fp toco rp 1 dan shopee rp 109', function () {
    $toco = checkoutService()->calculateCheckoutTotal([['final_price_bersih' => 100000, 'qty' => 1]], 0, 'fp', 'toco', 'bca');
    $shopee = checkoutService()->calculateCheckoutTotal([['final_price_bersih' => 100000, 'qty' => 1]], 0, 'fp', 'shopee', 'bca');

    expect($toco['fee_co_marketplace'])->toEqual(1)
        ->and($shopee['fee_co_marketplace'])->toEqual(109);
});

test('qty berlipat dihitung benar di subtotal', function () {
    $result = checkoutService()->calculateCheckoutTotal(
        [['final_price_bersih' => 85000, 'qty' => 3]],
        0,
        'fp',
        'toco',
        'bca'
    );

    expect($result['subtotal'])->toEqual(255000.0)
        ->and($result['total_barang'])->toEqual(255000.0);
});

test('maksimal diskon koin 5 persen subtotal dibulatkan ke bawah', function () {
    expect(checkoutService()->maxCoinDiscount(340000))->toEqual(17000.0)
        ->and(checkoutService()->maxCoinDiscount(99))->toEqual(4.0)
        ->and(checkoutService()->maxCoinDiscount(200000))->toEqual(10000.0);
});
