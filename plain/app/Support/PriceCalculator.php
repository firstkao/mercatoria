<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Converts a variant's yuan price into the rupiah selling price.
 *
 * Net price = (yuan + China shipping) × exchange rate + weight(kg) × (CN–ID rate + Batam–Jakarta rate).
 * Selling price = net price × (1 + margin), rounded up to the rounding step.
 */
final class PriceCalculator
{
    public function __construct(
        private ?float $exchangeRate,
        private ?float $cnIdRatePerKg,
        private ?float $btmJktRatePerKg,
        private float $marginPercent,
        private int $roundingStep,
    ) {}

    public static function fromSettings(): self
    {
        $number = fn (string $key): ?float => is_numeric(Setting::get($key)) ? (float) Setting::get($key) : null;

        return new self(
            $number('exchange_rate'),
            $number('cn_id_rate_per_kg'),
            $number('btm_jkt_rate_per_kg'),
            $number('margin_percent') ?? 11.0,
            max(1, Setting::integer('price_rounding', 5000)),
        );
    }

    /**
     * Determine whether the admin has filled in the exchange rate and shipping rates.
     */
    public function isConfigured(): bool
    {
        return $this->exchangeRate !== null && $this->cnIdRatePerKg !== null && $this->btmJktRatePerKg !== null;
    }

    /**
     * Get the in-China shipping share for one item, in yuan.
     */
    public function chinaShippingYuan(float $priceYuan, float $tierFeeYuan, float $tierMinimumYuan): float
    {
        if ($tierFeeYuan <= 0 || $tierMinimumYuan <= 0 || $priceYuan >= $tierMinimumYuan) {
            return 0.0;
        }

        return $priceYuan * $tierFeeYuan / $tierMinimumYuan;
    }

    /**
     * Get the rupiah selling price, or null while rates are not configured.
     */
    public function sellingPrice(float $priceYuan, int $weightGrams, float $tierFeeYuan, float $tierMinimumYuan): ?int
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $chinaShipping = $this->chinaShippingYuan($priceYuan, $tierFeeYuan, $tierMinimumYuan);
        $netPrice = ($priceYuan + $chinaShipping) * $this->exchangeRate
            + ($weightGrams / 1000) * ($this->cnIdRatePerKg + $this->btmJktRatePerKg);
        $sellingPrice = $netPrice * (1 + $this->marginPercent / 100);

        // Rounding first keeps float noise (e.g. 310800.0000001) from jumping a whole step.
        return (int) (ceil(round($sellingPrice / $this->roundingStep, 6)) * $this->roundingStep);
    }

    public static function formatRupiah(int $amount): string
    {
        return 'Rp'.number_format($amount, 0, ',', '.');
    }
    
     /**
     * Settings used by the admin form's live price preview.
     *
     * @return array{exchangeRate: ?float, cnIdRatePerKg: ?float, btmJktRatePerKg: ?float, marginPercent: float, roundingStep: int}
     */
    public function toArray(): array
    {
        return [
            'exchangeRate' => $this->exchangeRate,
            'cnIdRatePerKg' => $this->cnIdRatePerKg,
            'btmJktRatePerKg' => $this->btmJktRatePerKg,
            'marginPercent' => $this->marginPercent,
            'roundingStep' => $this->roundingStep,
        ];
    }
}