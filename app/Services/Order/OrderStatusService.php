<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public function changeStatus(
        Order $order,
        OrderStatus $newStatus,
        ?string $note = null,
        ?int $changedBy = null
    ): Order {
        if (! $order->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Tidak bisa ubah status dari '{$order->status}' ke '{$newStatus->value}'"
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $note, $changedBy) {
            $oldStatus = $order->status;
            $order->status = $newStatus->value;

            if ($newStatus === OrderStatus::MENUNGGU_PELUNASAN) {
                $order->pickup_deadline_at = now()->addDays(7);
            }

            if ($newStatus === OrderStatus::HANGUS) {
                $order->hangus_at = now();
            }

            $order->save();

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $newStatus->value,
                'note' => $note,
                'changed_by' => $changedBy ?? auth()->id(),
            ]);

            return $order->fresh();
        });
    }
}
