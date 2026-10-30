<?php

namespace App\Services\Order;

use App\Models\CoinTransaction;
use App\Models\Crush;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Refund;
use App\Models\User;
use App\Services\SplitBox\SplitBoxService;
use App\Services\User\CoinWalletService;
use App\Services\User\DiscountForfeitureService;
use App\Services\User\UserNotificationService;
use App\Services\User\UserTierService;

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

        // Gate membaca status terkini di DB (sumber kebenaran), bukan model yang mungkin stale.
        $order->refresh();
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

        // PRD 43: perubahan status order → bell ke detail order.
        app(UserNotificationService::class)->notify(
            $order->user,
            'order_status',
            'Status pesanan '.$order->no_order.' kini '.ucwords(str_replace('_', ' ', $toValue)),
            '/orders/'.$order->no_order
        );

        $order->refresh();
        $this->applySideEffects($order, $toValue);

        return $order;
    }

    private function applySideEffects(Order $order, string $toValue): void
    {
        if (in_array($toValue, [OrderStatus::PembayaranGagal->value, OrderStatus::Hangus->value, OrderStatus::HangusPermanen->value], true)) {
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

        // PRD 50: hitung tier saat Sedang Diproses (atau sesudahnya — idempotent via tier_counted_at).
        if (in_array($toValue, [
            OrderStatus::SedangDiproses->value,
            OrderStatus::SampaiWhChina->value,
            OrderStatus::DikirimKeIndonesia->value,
            OrderStatus::BeaCukai->value,
            OrderStatus::SampaiWhIndonesia->value,
            OrderStatus::MenungguPelunasanPengambilan->value,
            OrderStatus::SedangDikemas->value,
            OrderStatus::Selesai->value,
        ], true)) {
            app(UserTierService::class)->countOrder($order);
        }

        // PRD 50: Dana Dikembalikan — full = reversal penuh, partial = sesuai nominal refund.
        if ($toValue === OrderStatus::DanaDikembalikan->value) {
            $refund = Refund::query()
                ->where('order_id', $order->id)
                ->where('status', Refund::STATUS_COMPLETED)
                ->latest('id')
                ->first();

            app(UserTierService::class)->uncount(
                $order,
                $refund?->type === Refund::TYPE_PARTIAL ? (float) $refund->amount : null
            );
        }

        // PRD 50: Hangus / Hangus Permanen — reversal penuh.
        if (in_array($toValue, [
            OrderStatus::Hangus->value,
            OrderStatus::HangusPermanen->value,
        ], true)) {
            app(UserTierService::class)->uncount($order);
        }

        if ($toValue === OrderStatus::PembayaranDiterima->value) {
            $this->creditRefereeFirstPayment($order);
            $this->promoteCrushToCustomer($order);
        }

        if ($toValue === OrderStatus::Selesai->value) {
            $this->creditReferrerFirstOrder($order);
            $this->creditOrderCompletion($order);
        }
    }

    /**
     * PRD 7.8: "Crush —[order pertama verified]—> Customer (MVP)".
     * Trigger = pembayaran pertama terverifikasi (Pembayaran Diterima).
     */
    private function promoteCrushToCustomer(Order $order): void
    {
        $user = $order->user;

        if (! $user || $user->role !== User::ROLE_CRUSH) {
            return;
        }

        $user->update(['role' => User::ROLE_CUSTOMER]);

        Customer::firstOrCreate(
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

        Crush::query()->where('user_id', $user->id)->delete();
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
