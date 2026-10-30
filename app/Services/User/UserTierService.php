<?php

namespace App\Services\User;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;

class UserTierService
{
    /**
     * Hitung order ke progress tier saat mencapai Sedang Diproses (PRD 50).
     * Nilai penuh orders.total (termasuk DP 50%). Idempotent via orders.tier_counted_at.
     */
    public function countOrder(Order $order): void
    {
        if ($order->tier_counted_at !== null) {
            return;
        }

        $amount = (float) $order->total;

        $order->update([
            'tier_counted_at' => now(),
            'tier_counted_amount' => $amount,
        ]);

        $customer = $this->customerFor($order->user);
        $customer->increment('total_belanja', $amount);

        $this->applyTier($order->user, (float) $customer->fresh()->total_belanja);
    }

    /**
     * Kurangi total belanja (refund full/partial, hangus — PRD 50).
     */
    public function uncount(Order $order, ?float $amount = null): void
    {
        if ($order->tier_counted_at === null) {
            return;
        }

        $deduction = $amount ?? (float) ($order->tier_counted_amount ?? 0);

        $customer = Customer::where('user_id', $order->user_id)->first();

        if ($customer) {
            $customer->decrement('total_belanja', min($deduction, (float) $customer->total_belanja));
            $this->applyTier($order->user, (float) $customer->fresh()->total_belanja);
        }

        if ($amount === null) {
            $order->update(['tier_counted_at' => null, 'tier_counted_amount' => null]);
        } else {
            // PRD 50 (Partial Refund): nilai terhitung ikut berkurang sesuai nominal refund (konsisten untuk audit).
            $order->update([
                'tier_counted_amount' => max(0, (float) ($order->tier_counted_amount ?? 0) - $deduction),
            ]);
        }
    }

    /**
     * PRD 50: "Denda terverifikasi (tebus Hangus) — + nilai order kembali ke total belanja.
     * Pembayaran denda sendiri tidak dihitung ke progress tier."
     */
    public function restoreForPaidPenalty(Order $order): void
    {
        if ($order->tier_counted_at !== null) {
            return;
        }

        $order->update([
            'tier_counted_at' => now(),
            'tier_counted_amount' => (float) $order->total,
        ]);

        $customer = $this->customerFor($order->user);
        $customer->increment('total_belanja', (float) $order->total);

        $this->applyTier($order->user, (float) $customer->fresh()->total_belanja);
    }

    /**
     * Rekalkulasi tier dari total_belanja (PRD 50 / 9.8, threshold dari settings).
     */
    public function applyTier(User $user, float $totalBelanja): string
    {
        $gold = (float) setting('tier_gold_threshold', 2000000);
        $platinum = (float) setting('tier_platinum_threshold', 5000000);

        $tier = match (true) {
            $totalBelanja > $platinum => 'platinum',
            $totalBelanja > $gold => 'gold',
            default => 'silver',
        };

        $customer = $this->customerFor($user);
        $customer->update(['tier' => $tier]);

        return $tier;
    }

    /**
     * Data progress bar tier (PRD 50): posisi antara batas tier saat ini dan berikutnya.
     * Badge tier baru muncul setelah ada perhitungan (total_belanja > 0).
     *
     * @return array{current_tier: ?string, next_tier: ?string, progress_pct: float, belanja_sisa: ?float}
     */
    public function progress(Customer $customer): array
    {
        $total = (float) $customer->total_belanja;
        $gold = (float) setting('tier_gold_threshold', 2000000);
        $platinum = (float) setting('tier_platinum_threshold', 5000000);

        if ($total <= 0) {
            return ['current_tier' => null, 'next_tier' => 'silver', 'progress_pct' => 0.0, 'belanja_sisa' => null];
        }

        // Threshold "lebih besar dari" (>) — sisa = selisih + 1.
        [$current, $next, $nextThreshold, $floor] = match (true) {
            $total > $platinum => ['platinum', null, null, null],
            $total > $gold => ['gold', 'platinum', $platinum, $gold],
            default => ['silver', 'gold', $gold, 0.0],
        };

        if ($next === null) {
            return ['current_tier' => $current, 'next_tier' => null, 'progress_pct' => 100.0, 'belanja_sisa' => 0.0];
        }

        $span = $nextThreshold - $floor;

        return [
            'current_tier' => $current,
            'next_tier' => $next,
            'progress_pct' => round(min(100, max(0, ($total - $floor) / $span * 100)), 1),
            'belanja_sisa' => max(0, $nextThreshold - $total) + 1,
        ];
    }

    private function customerFor(User $user): Customer
    {
        return Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'current_state' => 'MVP',
                'tier' => 'silver',
                'total_belanja' => 0,
                'state_updated_at' => now(),
            ]
        );
    }
}
