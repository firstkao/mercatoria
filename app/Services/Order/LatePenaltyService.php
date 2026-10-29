<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Services\User\UserTierService;

class LatePenaltyService
{
    /**
     * Denda = late_penalty_per_day × hari sejak hangus_at (dibulatkan ke atas, minimal 1 hari).
     * PRD 51.1.
     */
    public function calculate(Order $order): int
    {
        $perDay = (int) setting('late_penalty_per_day', 1000);

        // Dibulatkan ke atas per hari (sebagian hari = 1 hari penuh), minimal 1 hari (PRD 51.1).
        // Sisa < 60 detik dibuang agar delta mikrodetik/waktu proses tidak menambah hari.
        $seconds = $order->hangus_at ? (int) $order->hangus_at->diffInSeconds(now()) : 0;
        $seconds -= $seconds % 60;
        $days = intdiv($seconds, 86400) + (($seconds % 86400) !== 0 ? 1 : 0);

        return $perDay * max(1, $days);
    }

    /**
     * PRD 51.1: "Buka Kunci via Denda" — nominal dibekukan ke order, status Menunggu Denda,
     * batas bayar default late_penalty_pay_hours.
     */
    public function unlockViaPenalty(Order $order, User $admin, ?int $nominal = null): Order
    {
        $amount = $nominal ?? $this->calculate($order);

        $order->update([
            'penalty_amount' => $amount,
            'penalty_unlocked_at' => now(),
            'penalty_deadline_at' => now()->addHours((int) setting('late_penalty_pay_hours', 24)),
        ]);

        return app(OrderStatusService::class)->changeStatus(
            $order,
            OrderStatus::MenungguDenda,
            'admin',
            "Buka Kunci via Denda sebesar {$amount}"
        );
    }

    /**
     * PRD 51.1: denda terverifikasi → kembali ke Menunggu Pelunasan & Pengambilan dengan timer CO baru.
     * Pembayaran denda TIDAK dihitung ke progress tier (PRD 50).
     */
    public function markPaid(Order $order): Order
    {
        $order->update(['penalty_paid_at' => now()]);

        $order = app(OrderStatusService::class)->changeStatus(
            $order,
            OrderStatus::MenungguPelunasanPengambilan,
            'admin',
            'Denda lunas — tebus Hangus'
        );

        app(UserTierService::class)->restoreForPaidPenalty($order);

        return $order;
    }

    /**
     * PRD 51.1: lewat batas bayar → kembali ke Hangus (hitungan denda terus berjalan).
     */
    public function expireOverdue(): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::MenungguDenda->value)
            ->whereNotNull('penalty_deadline_at')
            ->where('penalty_deadline_at', '<=', now())
            ->get();

        $statuses = app(OrderStatusService::class);

        foreach ($orders as $order) {
            $statuses->changeStatus($order, OrderStatus::Hangus, 'system', 'Denda tidak dibayar sampai batas');
        }

        return $orders->count();
    }

    /**
     * PRD 51.1: Admin dapat menetapkan Hangus Permanen kapan saja.
     */
    public function markHangusPermanen(Order $order, User $admin): Order
    {
        // Skema DB 18: timestamp penetapan Hangus Permanen.
        $order->update(['hangus_permanent_at' => now()]);

        return app(OrderStatusService::class)->changeStatus(
            $order,
            OrderStatus::HangusPermanen,
            'admin',
            'Hangus Permanen'
        );
    }
}
