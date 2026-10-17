<?php

namespace App\Services\Order;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Services\Notification\UserNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(private readonly OrderStatusService $statuses) {}

    public function request(Order $order, User $user, float $amount, string $reason): Refund
    {
        if ($order->user_id !== $user->id) {
            abort(404);
        }

        return DB::transaction(function () use ($order, $user, $amount, $reason): Refund {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $paid = $order->paidAmount();
            $existing = (float) $order->refunds()
                ->whereIn('status', [RefundStatus::REQUESTED->value, RefundStatus::PROCESSING->value, RefundStatus::COMPLETED->value])
                ->sum('amount');

            if ($paid <= 0 || $amount <= 0 || $amount > $paid - $existing) {
                throw ValidationException::withMessages([
                    'amount' => 'Nilai refund harus positif dan tidak melebihi pembayaran terverifikasi yang belum direfund.',
                ]);
            }
            if (! $order->canTransitionTo(OrderStatus::DANA_DIKEMBALIKAN)) {
                throw ValidationException::withMessages(['order' => 'Status pesanan ini tidak dapat diajukan untuk refund.']);
            }
            if ($order->payments()->where('is_locked', true)->exists()) {
                throw ValidationException::withMessages(['order' => 'Tunggu verifikasi pembayaran yang sedang berlangsung sebelum mengajukan refund.']);
            }

            $refund = $order->refunds()->create([
                'type' => $amount >= $paid - $existing ? 'full' : 'partial',
                'amount' => $amount,
                'reason' => $reason,
                'status' => RefundStatus::REQUESTED->value,
            ]);
            AdminNotification::create([
                'type' => 'refund_request',
                'title' => 'Permintaan refund',
                'message' => "Refund {$order->order_no} sebesar Rp ".number_format($amount, 0, ',', '.').'.',
                'link_url' => route('office.refunds.index'),
                'meta' => ['order_id' => $order->id, 'refund_id' => $refund->id],
            ]);
            UserNotificationService::push(
                $user->id,
                NotificationType::ORDER,
                'Permintaan refund diterima',
                "Refund pesanan {$order->order_no} sedang diproses.",
                route('orders.show', $order),
                subtype: 'refund_requested',
                meta: ['order_id' => $order->id, 'refund_id' => $refund->id],
            );

            return $refund;
        });
    }

    public function process(Refund $refund, User $admin, bool $approve, ?string $adminNote): Refund
    {
        $refund = DB::transaction(function () use ($refund, $admin, $approve, $adminNote): Refund {
            $refund = Refund::query()->lockForUpdate()->findOrFail($refund->id);
            if (! in_array($refund->status, [RefundStatus::REQUESTED->value, RefundStatus::PROCESSING->value], true)) {
                throw ValidationException::withMessages(['refund' => 'Permintaan refund ini sudah diproses.']);
            }
            $order = Order::query()->lockForUpdate()->findOrFail($refund->order_id);
            if ($approve && ! $order->canTransitionTo(OrderStatus::DANA_DIKEMBALIKAN)) {
                throw ValidationException::withMessages(['order' => 'Status pesanan sudah berubah dan tidak dapat direfund.']);
            }
            $refund->update([
                'status' => $approve ? RefundStatus::COMPLETED->value : RefundStatus::REJECTED->value,
                'admin_note' => $adminNote,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            return $refund->fresh('order');
        });

        if ($approve) {
            $this->statuses->changeStatus($refund->order, OrderStatus::DANA_DIKEMBALIKAN, 'Refund diselesaikan manual oleh admin.', $admin->id);
        }

        UserNotificationService::push(
            $refund->order->user_id,
            NotificationType::ORDER,
            $approve ? 'Refund selesai' : 'Permintaan refund ditolak',
            $approve
                ? "Refund pesanan {$refund->order->order_no} selesai. Dana sudah ditransfer."
                : "Refund pesanan {$refund->order->order_no} ditolak. Alasan: ".($adminNote ?: 'Hubungi customer service.'),
            route('orders.show', $refund->order),
            subtype: $approve ? 'refund_completed' : 'refund_rejected',
            meta: ['order_id' => $refund->order_id, 'refund_id' => $refund->id],
        );

        return $refund;
    }
}
