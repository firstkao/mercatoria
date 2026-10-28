<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;

class OrderStatusService
{
    /**
     * Ubah status order lewat satu pintu (PRD 18) + tulis order_status_logs (PRD 18.6).
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

        return $order->fresh();
    }
}
