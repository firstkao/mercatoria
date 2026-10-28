<?php

namespace App\Services\Order;

use App\Models\Product;
use App\Models\ProductVariant;

class ShippingCalculatorService
{
    /**
     * Ongkir lokal CN (Rp) per varian/produk (PRD 47.5).
     * Rumus: (harga_rmb × ongkir_store) / min_belanja_rmb × rate.
     */
    public function calculateLocalCn(
        Product $product,
        ?ProductVariant $variant = null
    ): float {
        $store = $product->effectiveStore();

        if (! $store) {
            return 0.0;
        }

        if ($store->isFreeShipping()) {
            return 0.0;
        }

        if (! $store->hasMinBelanja()) {
            return 0.0;
        }

        $hargaRmb = $variant
            ? $variant->effectivePriceRmb()
            : (float) $product->price_rmb;

        $minBelanja = (float) $store->min_belanja_rmb;
        $ongkirRmb = (float) $store->ongkir_rmb;

        if ($hargaRmb >= $minBelanja) {
            return 0.0;
        }

        $ongkirLokalRmb = ($hargaRmb * $ongkirRmb) / $minBelanja;
        $rate = (float) setting('rate_rmb_idr', 3000);

        return round($ongkirLokalRmb * $rate, 2);
    }

    /**
     * Ongkir CN → Batam (Rp) dari berat (PRD 47.6).
     */
    public function calculateCnBtm(int $weightGram): float
    {
        $rate = (float) setting('ongkir_cn_btm_per_kg', 25000);

        return ($weightGram / 1000) * $rate;
    }

    /**
     * Ongkir Batam → Jakarta (Rp) dari berat (PRD 47.6).
     */
    public function calculateBtmJkt(int $weightGram): float
    {
        $rate = (float) setting('ongkir_btm_jkt_per_kg', 60000);

        return ($weightGram / 1000) * $rate;
    }

    /**
     * Breakdown ongkir + Harga Bersih 1 item/varian (PRD 47.7A).
     * Profit 11% + CEILING kelipatan 5.000 — dibulatkan SEKALI di sini.
     *
     * @return array{
     *     price_rmb: float,
     *     harga_produk_idr: float,
     *     weight_gram: int,
     *     store_name: ?string,
     *     ongkir_lokal_cn_idr: float,
     *     ongkir_cn_btm_idr: float,
     *     ongkir_btm_jkt_idr: float,
     *     ongkir_total_idr: float,
     *     profit_idr: float,
     *     base_price_bersih: float,
     *     discount_amount: float,
     *     final_price_bersih: float
     * }
     */
    public function calculateItemShipping(
        Product $product,
        ?ProductVariant $variant = null
    ): array {
        $weightGram = $variant
            ? $variant->effectiveWeight()
            : $product->effectiveWeight();
        $hargaRmb = $variant
            ? $variant->effectivePriceRmb()
            : (float) $product->price_rmb;
        $rate = (float) setting('rate_rmb_idr', 3000);
        $profitPct = ((float) setting('profit_percent', 11)) / 100;
        $rounding = (int) setting('price_rounding', 5000);

        $hargaProdukIdr = $hargaRmb * $rate;
        $lokalCn = $this->calculateLocalCn($product, $variant);
        $cnBtm = $this->calculateCnBtm($weightGram);
        $btmJkt = $this->calculateBtmJkt($weightGram);
        $ongkirTotal = $lokalCn + $cnBtm + $btmJkt;

        $modalItem = $hargaProdukIdr + $ongkirTotal;
        $profitIdr = $modalItem * $profitPct;
        $rawTotal = $modalItem + $profitIdr;

        $basePriceBersih = (float) (ceil($rawTotal / $rounding) * $rounding);
        $discountAmount = $product->hasActiveDiscount()
            ? (float) $product->discount_amount
            : 0.0;
        $finalPriceBersih = max(0, $basePriceBersih - $discountAmount);

        return [
            'price_rmb' => round($hargaRmb, 2),
            'harga_produk_idr' => round($hargaProdukIdr, 2),
            'weight_gram' => $weightGram,
            'store_name' => $product->effectiveStore()?->name,
            'ongkir_lokal_cn_idr' => round($lokalCn, 2),
            'ongkir_cn_btm_idr' => round($cnBtm, 2),
            'ongkir_btm_jkt_idr' => round($btmJkt, 2),
            'ongkir_total_idr' => round($ongkirTotal, 2),
            'profit_idr' => round($profitIdr, 2),
            'base_price_bersih' => $basePriceBersih,
            'discount_amount' => $discountAmount,
            'final_price_bersih' => $finalPriceBersih,
        ];
    }

    /**
     * Total order di checkout (PRD 20.6) — TANPA CEILING lagi.
     * Harga item sudah fix (dibulatkan sekali di calculateItemShipping).
     *
     * @param  array<int, array{final_price_bersih: float|int, qty: int}>  $items
     * @return array{
     *     subtotal: float,
     *     discount: float,
     *     total_barang: float,
     *     fee_qris: float,
     *     tagihan_web: float,
     *     estimasi_co_marketplace?: float,
     *     fee_co_marketplace?: float
     * }
     */
    public function calculateCheckoutTotal(
        array $items,
        float $discount = 0,
        string $scheme = 'fp',
        string $platform = 'toco',
        string $method = 'bca'
    ): array {
        $subtotal = (float) collect($items)->sum(fn ($i) => $i['final_price_bersih'] * $i['qty']);
        $totalBarang = max(0, $subtotal - $discount);
        $feeQris = ($method === 'qris' && $totalBarang >= 500000)
            ? $totalBarang * 0.0003
            : 0.0;

        if ($scheme === 'dp') {
            $dp = $totalBarang * 0.5;
            $sisa = $totalBarang - $dp;
            $shopeePercent = ((float) setting('fee_shopee_dp', 25)) / 100;

            return [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_barang' => $totalBarang,
                'fee_qris' => $feeQris,
                'tagihan_web' => $dp + $feeQris,
                'estimasi_co_marketplace' => $platform === 'shopee'
                    ? $sisa * (1 + $shopeePercent)
                    : $sisa + (float) setting('fee_toco_dp', 0),
            ];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total_barang' => $totalBarang,
            'fee_qris' => $feeQris,
            'tagihan_web' => $totalBarang + $feeQris,
            'fee_co_marketplace' => (float) ($platform === 'shopee'
                ? setting('fee_shopee_fp', 109)
                : setting('fee_toco_fp', 1)),
        ];
    }

    /**
     * Maksimal diskon koin = 5% dari subtotal (PRD 9.3 / 20.6), dibulatkan ke bawah.
     */
    public function maxCoinDiscount(float $subtotal): float
    {
        return floor($subtotal * 0.05);
    }
}
