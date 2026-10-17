<?php

namespace App\Services\Order;

use App\Models\Product;
use App\Models\ProductVariant;

class ShippingCalculatorService
{
    /**
     * Ongkir lokal CN (per produk) dalam IDR
     */
    public function calculateLocalCn(Product $product): float
    {
        $store = $product->effectiveStore();

        if (! $store) return 0;
        if ($store->isFreeShipping()) return 0;
        if (! $store->hasMinBelanja()) return 0;

        $hargaRmb = (float) $product->price_rmb;
        $minBelanja = (float) $store->min_belanja_rmb;
        $ongkirRmb = (float) $store->ongkir_rmb;

        if ($hargaRmb >= $minBelanja) return 0;

        $ongkirLokalRmb = ($hargaRmb * $ongkirRmb) / $minBelanja;
        $rate = (float) setting('rate_rmb_idr', 2200);

        return $ongkirLokalRmb * $rate;
    }

    /**
     * Ongkir CN → Batam (per item) dalam IDR
     */
    public function calculateCnBtm(int $weightGram): float
    {
        $rate = (float) setting('ongkir_cn_btm_per_kg', 25000);
        $beratKg = $weightGram / 1000;
        return $beratKg * $rate;
    }

    /**
     * Ongkir Batam → Jakarta (per item) dalam IDR
     */
    public function calculateBtmJkt(int $weightGram): float
    {
        $rate = (float) setting('ongkir_btm_jkt_per_kg', 60000);
        $beratKg = $weightGram / 1000;
        return $beratKg * $rate;
    }

    /**
     * Breakdown lengkap untuk 1 item
     */
    public function calculateItemShipping(
        Product $product,
        ?ProductVariant $variant = null
    ): array {
        $weightGram = $variant?->effectiveWeight() ?? $product->effectiveWeight();

        $lokalCn = $this->calculateLocalCn($product);
        $cnBtm = $this->calculateCnBtm($weightGram);
        $btmJkt = $this->calculateBtmJkt($weightGram);

        return [
            'weight_gram' => $weightGram,
            'store_name' => $product->effectiveStore()?->name,
            'ongkir_lokal_cn_idr' => round($lokalCn, 2),
            'ongkir_cn_btm_idr' => round($cnBtm, 2),
            'ongkir_btm_jkt_idr' => round($btmJkt, 2),
            'ongkir_total_idr' => round($lokalCn + $cnBtm + $btmJkt, 2),
        ];
    }
}
