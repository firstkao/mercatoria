<?php

namespace App\Services\Order;

use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Services\SplitBox\SplitBoxService;
use App\Services\User\CoinWalletService;
use App\Services\User\DiscountForfeitureService;

class OrderStatusService
{
    public function __construct(
        private DiscountForfeitureService $forfeiture,
        private CoinWalletService $coins,
    ) {}

    /**
     * Ubah status order lewat satu pintu (PRD 18) + tulis order_status_logs (PRD 18.6).
     *
     * Side-effect koin/voucher (PRD 9.2–9.3):
     * - pembayaran_gagal / hangus → forfeitUnpaidCheckoutDiscounts (hangus permanen, tanpa undo)
     * - pembayaran_diterima → +1.000 koin referee (pembayaran pertama terverifikasi)
     * - selesai → +3.000 koin referrer (order pertama referee) + 1% koin pembeli
     *
     * @param  string  $actor  user|admin|system (OrderStatusLog::ACTOR_*)
     *
     * @throws \RuntimeException bila transisi tidak sah
     */
    public function changeStatus(Order $order, OrderStatus|string $to, string $actor, ?string $note = null): Order
    {
        $toValue = $to instanceof OrderStatus ? $to->value : $to;
        $from = OrderStatus::tryFrom($order->status);

        if (! $from) {
            throw new \RuntimeException("Status order tidak dikenal: {$order->status}");
        }

        if (! $from->canTransitionTo($toValue)) {
            throw new \RuntimeException("Transisi status tidak sah: {$order->status} → {$toValue}");
        }

        $order->status = $toValue;

        if ($toValue === OrderStatus::MenungguPelunasanPengambilan->value) {
            $order->pickup_deadline_at = now()->addDays((int) setting('late_penalty_pickup_days', 7));
        }

        if ($toValue === OrderStatus::MenungguPembayaran->value) {
            // Reset batas upload bukti 1×24 jam (PRD 17.10 — setelah admin menolak).
            $order->payment_deadline_at = now()->addHours(24);
        }

        $order->save();

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $toValue,
            'changed_by' => $actor,
            'note' => $note,
        ]);

        $order->refresh();
        $this->applySideEffects($order, $toValue);

        return $order;
    }

    private function applySideEffects(Order $order, string $toValue): void
    {
        if (in_array($toValue, [OrderStatus::PembayaranGagal->value, OrderStatus::Hangus->value], true)) {
            $this->forfeiture->forfeitUnpaidCheckoutDiscounts($order);
        }

        // PRD 49.6: slot dilepas saat reject / gagal / refund — TIDAK saat Hangus.
        if (in_array($toValue, [
            OrderStatus::MenungguPembayaran->value,
            OrderStatus::PembayaranGagal->value,
            OrderStatus::DanaDikembalikan->value,
        ], true)) {
            app(SplitBoxService::class)->releaseSlotsForOrder($order);
        }

        if ($toValue === OrderStatus::PembayaranDiterima->value) {
            $this->creditRefereeFirstPayment($order);
        }

        if ($toValue === OrderStatus::Selesai->value) {
            $this->creditReferrerFirstOrder($order);
            $this->creditOrderCompletion($order);
        }
    }

    /**
     * PRD 9.2: "Referee daftar via link — 1.000 — Pembayaran pertama referee terverifikasi".
     */
    private function creditRefereeFirstPayment(Order $order): void
    {
        $referee = $order->user;

        if (! $referee?->referred_by) {
            return;
        }

        $alreadyCredited = CoinTransaction::query()
            ->where('user_id', $referee->id)
            ->where('type', CoinTransaction::TYPE_EARNED_REFERRAL_REFEREE)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $this->coins->credit(
            $referee,
            1000,
            $order,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFEREE,
            "Bonus referee — pembayaran pertama order {$order->no_order} (PRD 9.2)"
        );
    }

    /**
     * PRD 9.2: "Undang teman (referral) — 3.000 — Referee selesaikan order pertama".
     */
    private function creditReferrerFirstOrder(Order $order): void
    {
        $referee = $order->user;

        if (! $referee?->referred_by) {
            return;
        }

        // Hanya order pertama referee yang mencapai Selesai.
        $completedCount = Order::query()
            ->where('user_id', $referee->id)
            ->where('status', OrderStatus::Selesai->value)
            ->count();

        if ($completedCount !== 1) {
            return;
        }

        $referrer = User::find($referee->referred_by);

        if (! $referrer || $this->isSelfReferral($referrer, $referee)) {
            return;
        }

        $this->coins->credit(
            $referrer,
            3000,
            $order,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFERRER,
            "Bonus referral — referee {$referee->id} selesaikan order pertama (PRD 9.2)"
        );
    }

    /**
     * PRD 9.2: "Order selesai — 1% × total transaksi — Pembeli — Order status Selesai".
     * Basis: orders.total (nilai penuh, konsisten PRD 50). Dibulatkan ke bawah.
     */
    private function creditOrderCompletion(Order $order): void
    {
        $alreadyCredited = CoinTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', CoinTransaction::TYPE_EARNED_ORDER)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $amount = (int) floor((float) $order->total * 0.01);

        if ($amount <= 0) {
            return;
        }

        $this->coins->credit(
            $order->user,
            $amount,
            $order,
            CoinTransaction::TYPE_EARNED_ORDER,
            "1% dari order {$order->no_order} (PRD 9.2)"
        );
    }

    /**
     * Pencegahan self-referral: orang yang sama (id atau no HP) tidak dapat bonus.
     */
    private function isSelfReferral(User $referrer, User $referee): bool
    {
        return $referrer->id === $referee->id
            || normalize_phone((string) $referrer->no_hp) === normalize_phone((string) $referee->no_hp);
    }
}
