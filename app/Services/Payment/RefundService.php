<?php

namespace App\Services\Payment;

use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserVoucher;
use App\Services\Admin\AdminNotificationService;
use App\Services\Order\OrderStatusService;
use App\Services\User\CoinWalletService;
use App\Services\User\UserNotificationService;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(
        private CoinWalletService $coins,
        private UserNotificationService $notifications,
    ) {}

    public function requestFull(Order $order, string $reason, User $admin): Refund
    {
        return $this->request($order, Refund::TYPE_FULL, (float) $order->tagihan_web, $reason, $admin);
    }

    public function requestPartial(Order $order, float $amount, string $reason, User $admin): Refund
    {
        return $this->request($order, Refund::TYPE_PARTIAL, $amount, $reason, $admin);
    }

    public function complete(Refund $refund, User $admin): Refund
    {
        if ($refund->status !== Refund::STATUS_REQUESTED) {
            throw new \RuntimeException('Refund sudah diproses sebelumnya.');
        }

        // ATOMIC (uang): koin/voucher + status refund + status order + notif — satu transaksi.
        return DB::transaction(function () use ($refund, $admin) {
            $order = $refund->order;

            // Koin/voucher dikembalikan SEBELUM status dana_dikembalikan —
            // jalur terpisah dari DiscountForfeitureService (forfeit tetap berlaku untuk
            // pembayaran_gagal/hangus, tidak tersentuh di sini).
            $this->returnDiscounts($order);

            $refund->update([
                'status' => Refund::STATUS_COMPLETED,
                'handled_by' => $admin->id,
                'completed_at' => now(),
            ]);

            app(OrderStatusService::class)->changeStatus(
                $order,
                OrderStatus::DanaDikembalikan->value,
                'admin',
                'Refund selesai'
            );

            $this->notifications->notify(
                $order->user,
                'refund_completed',
                $this->completedMessage($order),
                '/orders/'.$order->no_order
            );

            return $refund->refresh();
        });
    }

    public function reject(Refund $refund, string $reason, User $admin): Refund
    {
        if ($refund->status !== Refund::STATUS_REQUESTED) {
            throw new \RuntimeException('Refund sudah diproses sebelumnya.');
        }

        $order = $refund->order;

        $refund->update([
            'status' => Refund::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'handled_by' => $admin->id,
        ]);

        $this->notifications->notify(
            $order->user,
            'refund_rejected',
            'Pengembalian dana pesanan #'.$order->no_order.' ditolak. Alasan: '.$reason,
            '/orders/'.$order->no_order
        );

        return $refund->refresh();
    }

    /**
     * Split box (PRD 49.11): refund full OTOMATIS saat kloter ditutup tidak terpenuhi —
     * langsung completed tanpa fase requested, cukup 1 notif "dana sudah dikembalikan".
     */
    public function autoFull(Order $order, string $reason, User $admin): Refund
    {
        // ATOMIC (uang): koin/voucher + record refund + status order + notif — satu transaksi.
        return DB::transaction(function () use ($order, $reason, $admin) {
            $this->returnDiscounts($order);

            $refund = Refund::create([
                'order_id' => $order->id,
                'type' => Refund::TYPE_FULL,
                'amount' => (float) $order->tagihan_web,
                'reason' => $reason,
                'status' => Refund::STATUS_COMPLETED,
                'requested_by' => $admin->id,
                'handled_by' => $admin->id,
                'completed_at' => now(),
            ]);

            app(OrderStatusService::class)->changeStatus(
                $order,
                OrderStatus::DanaDikembalikan->value,
                'admin',
                $reason
            );

            $this->notifications->notify(
                $order->user,
                'refund_completed',
                $this->completedMessage($order),
                '/orders/'.$order->no_order
            );

            return $refund;
        });
    }

    private function request(Order $order, string $type, float $amount, string $reason, User $admin): Refund
    {
        $refund = Refund::create([
            'order_id' => $order->id,
            'type' => $type,
            'amount' => $amount,
            'reason' => $reason,
            'status' => Refund::STATUS_REQUESTED,
            'requested_by' => $admin->id,
        ]);

        // PRD 41.1: bell admin — refund request.
        app(AdminNotificationService::class)->notify(
            'refund_request',
            'Refund #'.$order->no_order.' diminta',
            '/office/orders/'.$order->no_order
        );

        $this->notifications->notify(
            $order->user,
            'refund_requested',
            'Pengembalian dana pesanan #'.$order->no_order.' sedang diproses.',
            '/orders/'.$order->no_order
        );

        return $refund;
    }

    /**
     * Keputusan refund: koin (debit checkout) & voucher DIPAKAI KEMBALI — bukan salah user.
     * Idempotent: koin hanya dikredit sekali per order; voucher hanya yang masih used_at_checkout.
     */
    private function returnDiscounts(Order $order): void
    {
        if ($order->coin_spent > 0) {
            $alreadyReturned = CoinTransaction::query()
                ->where('order_id', $order->id)
                ->where('type', CoinTransaction::TYPE_REFUND_RETURNED)
                ->exists();

            if (! $alreadyReturned) {
                $this->coins->credit(
                    $order->user,
                    (int) $order->coin_spent,
                    $order,
                    CoinTransaction::TYPE_REFUND_RETURNED,
                    'Pengembalian koin dari refund order '.$order->no_order
                );
            }
        }

        if ($order->user_voucher_id) {
            UserVoucher::where('id', $order->user_voucher_id)
                ->where('status', UserVoucher::STATUS_USED_AT_CHECKOUT)
                ->update(['status' => UserVoucher::STATUS_ACTIVE, 'used_at' => null]);
        }
    }

    private function completedMessage(Order $order): string
    {
        $message = 'Pengembalian dana pesanan #'.$order->no_order.' selesai. Dana sudah ditransfer.';

        $parts = [];
        if ($order->coin_spent > 0) {
            $parts[] = 'Koin dikembalikan';
        }
        if ($order->user_voucher_id) {
            $parts[] = 'Voucher dikembalikan';
        }

        return $parts ? $message.' '.implode(' & ', $parts).'.' : $message;
    }
}
