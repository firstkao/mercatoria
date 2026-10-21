<?php

namespace App\Services\Order;

use App\Models\Product;
use App\Models\ProductVariant;

class ShippingCalculatorService
{
    /**
     * Ongkir lokal CN (per produk / varian) dalam IDR
     */
    public function calculateLocalCn(Product $product, ?ProductVariant $variant = null): float
    {
        $store = $product->effectiveStore();

        if (! $store) {
            return 0;
        }
        if ($store->isFreeShipping()) {
            return 0;
        }
        if (! $store->hasMinBelanja()) {
            return 0;
        }

        $hargaRmb = $variant ? $variant->effectivePriceRmb() : (float) $product->price_rmb;
        $minBelanja = (float) $store->min_belanja_rmb;
        $ongkirRmb = (float) $store->ongkir_rmb;

        if ($hargaRmb >= $minBelanja) {
            return 0;
        }

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
     * Breakdown lengkap untuk 1 item/varian. Profit dan pembulatan CEILING
     * sudah termasuk dalam Harga Bersih sehingga checkout tidak menambah profit lagi.
     */
    public function calculateItemShipping(
        Product $product,
        ?ProductVariant $variant = null
    ): array {
        $weightGram = $variant?->effectiveWeight() ?? $product->effectiveWeight();
        $hargaRmb = $variant ? $variant->effectivePriceRmb() : (float) $product->price_rmb;
        $rate = (float) setting('rate_rmb_idr', 2200);
        $profitPct = ((float) setting('profit_percent', 11)) / 100;
        $rounding = max(1, (int) setting('price_rounding', config('mercatoria.order.rounding', 5000)));

        $hargaProdukIdr = $hargaRmb * $rate;
        $lokalCn = $this->calculateLocalCn($product, $variant);
        $cnBtm = $this->calculateCnBtm($weightGram);
        $btmJkt = $this->calculateBtmJkt($weightGram);
        $ongkirTotal = $lokalCn + $cnBtm + $btmJkt;

        $modalItem = $hargaProdukIdr + $ongkirTotal;
        $profitIdr = $modalItem * $profitPct;
        $basePriceBersih = (float) (ceil(($modalItem + $profitIdr) / $rounding) * $rounding);
        $discountAmount = $product->hasActiveDiscount() ? (float) $product->discount_amount : 0.0;
        $discountAmount = min($discountAmount, $basePriceBersih);

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
            'discount_amount' => round($discountAmount, 2),
            'final_price_bersih' => round($basePriceBersih - $discountAmount, 2),
        ];
    }
}
