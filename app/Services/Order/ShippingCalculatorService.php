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
}
