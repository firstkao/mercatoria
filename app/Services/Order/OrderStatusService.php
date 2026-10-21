<?php

namespace App\Services\Order;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\UserVoucher;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use App\Services\User\ReferralRewardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OrderStatusService
{
    public function __construct(private readonly ReferralRewardService $referralRewards) {}

    public function changeStatus(
        Order $order,
        OrderStatus $newStatus,
        ?string $note = null,
        ?int $changedBy = null
    ): Order {
        $updatedOrder = DB::transaction(function () use ($order, $newStatus, $note, $changedBy) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! $order->canTransitionTo($newStatus)) {
                throw new RuntimeException(
                    "Tidak bisa ubah status dari '{$order->status}' ke '{$newStatus->value}'"
                );
            }
            $oldStatus = $order->status;
            $order->status = $newStatus->value;

            if ($newStatus === OrderStatus::MENUNGGU_PELUNASAN) {
                $order->pickup_deadline_at = now()->addDays(7);
            }

            if ($newStatus === OrderStatus::HANGUS) {
                $order->hangus_at = now();
            }

            if (
                $newStatus === OrderStatus::PEMBAYARAN_GAGAL
                && ! $order->payments()->where('status', 'verified')->exists()
            ) {
                $this->releaseUnpaidCheckoutDiscounts($order);
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

        if ($newStatus !== OrderStatus::DANA_DIKEMBALIKAN) {
            UserNotificationService::push(
                $updatedOrder->user_id,
                NotificationType::ORDER,
                $newStatus === OrderStatus::SELESAI ? 'Pesanan selesai' : 'Status pesanan diperbarui',
                "Pesanan {$updatedOrder->order_no}: {$newStatus->label()}.",
                route('orders.show', $updatedOrder),
                meta: ['order_id' => $updatedOrder->id, 'status' => $newStatus->value],
            );
        }

        $emailCode = match ($newStatus) {
            OrderStatus::SELESAI => 'order_completed',
            OrderStatus::HANGUS => 'order_hangus',
            OrderStatus::MENUNGGU_PELUNASAN => 'settlement_reminder',
            OrderStatus::DANA_DIKEMBALIKAN => null,
            default => 'order_status_changed',
        };
        if ($emailCode) {
            app(ScheduledEmailService::class)->sendOrder(
                $updatedOrder,
                $emailCode,
                'Update status pesanan '.$updatedOrder->order_no,
            );
        }

        if ($newStatus === OrderStatus::MENUNGGU_PELUNASAN) {
            UserNotificationService::push(
                $updatedOrder->user_id,
                NotificationType::ORDER,
                'Tagihan pelunasan aktif — H-7',
                "Pesanan {$updatedOrder->order_no} siap dilunasi. Selesaikan sebelum {$updatedOrder->pickup_deadline_at?->format('d M Y H:i')}.",
                route('orders.show', $updatedOrder),
                subtype: 'pickup_h7',
                meta: ['order_id' => $updatedOrder->id, 'reminder_key' => 'pickup_h7'],
            );
            AdminNotification::create([
                'type' => 'order_nearing_expiry',
                'title' => 'Pesanan mulai menunggu pelunasan',
                'message' => "{$updatedOrder->order_no} memiliki tenggat 7 hari.",
                'link_url' => route('office.orders.show', $updatedOrder),
                'meta' => ['order_id' => $updatedOrder->id],
            ]);
        }

        if (in_array($newStatus, [
            OrderStatus::SELESAI,
            OrderStatus::DANA_DIKEMBALIKAN,
            OrderStatus::HANGUS,
        ], true)) {
            $this->deletePaymentProofs($updatedOrder);
        }

        if ($newStatus === OrderStatus::SELESAI) {
            $this->referralRewards->onOrderCompleted($updatedOrder);
        }

        return $updatedOrder;
    }

    private function releaseUnpaidCheckoutDiscounts(Order $order): void
    {
        $coinUsage = CoinTransaction::query()
            ->where('order_id', $order->id)
            ->where('source', CoinSource::USED_AT_CHECKOUT->value)
            ->where('amount', '<', 0)
            ->lockForUpdate()
            ->first();

        if ($coinUsage && ! CoinTransaction::query()
            ->where('order_id', $order->id)
            ->where('source', CoinSource::CHECKOUT_RELEASE->value)
            ->exists()) {
            $coin = Coin::query()->where('user_id', $order->user_id)->lockForUpdate()->first();
            if (! $coin) {
                throw new RuntimeException("Wallet untuk pesanan {$order->order_no} tidak ditemukan.");
            }

            $amount = abs((int) $coinUsage->amount);
            $coin->increment('balance', $amount);
            $coin->refresh();
            CoinTransaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'source' => CoinSource::CHECKOUT_RELEASE->value,
                'description' => "Koin dikembalikan dari pesanan {$order->order_no} yang gagal dibayar.",
                'balance_after' => $coin->balance,
                'expires_at' => now()->addYear(),
                'status' => 'active',
            ]);
        }

        if ($order->user_voucher_id) {
            $userVoucher = UserVoucher::query()
                ->whereKey($order->user_voucher_id)
                ->lockForUpdate()
                ->first();
            if ($userVoucher?->status === 'used' && (int) $userVoucher->order_id === (int) $order->id) {
                $userVoucher->update([
                    'status' => 'active',
                    'used_at' => null,
                    'order_id' => null,
                ]);
            }
        }
    }

    private function deletePaymentProofs(Order $order): void
    {
        $disk = Storage::disk('local');
        foreach ($order->payments()->whereNotNull('proof_path')->get() as $payment) {
            $path = $payment->proof_path;
            if ($disk->exists($path) && ! $disk->delete($path)) {
                Log::error('Failed to delete payment proof after terminal order status.', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'path' => $path,
                ]);
                throw new RuntimeException("Gagal menghapus bukti pembayaran: {$path}");
            }

            $payment->update(['proof_path' => null]);
        }
    }
}
