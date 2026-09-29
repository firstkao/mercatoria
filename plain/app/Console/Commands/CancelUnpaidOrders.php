<?php

namespace App\Console\Commands;

use App\Actions\DeleteSpammerAccount;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Membatalkan pesanan yang lewat batas pembayaran dan menghapus akun Spammer';

    public function handle(DeleteSpammerAccount $deleteSpammerAccount): int
    {
        $expiredOrders = Order::with('user')
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<', now())
            ->whereDoesntHave('paymentProofs', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->get();

        $canceledCount = 0;
        $deletedSpammers = 0;

        foreach ($expiredOrders as $order) {
            $oldStatus = $order->status;
            $notifyUser = null;

            DB::transaction(function () use ($order, $oldStatus, $deleteSpammerAccount, &$canceledCount, &$deletedSpammers, &$notifyUser) {
                $user = $order->user;

                if ($user && method_exists($user, 'isSpammer') && $user->isSpammer()) {
                    $deleteSpammerAccount->handle($user, 'deleted_unpaid_order');
                    $deletedSpammers++;
                    return;
                }

                // ===== Batalkan order =====
                $order->update([
                    'status' => 'dibatalkan',
                    'cancelled_at' => now(),
                ]);

                // Kembalikan koin yang terpakai
                $spentCoins = DB::table('coin_spends')->where('order_id', $order->id)->get();
                if ($spentCoins->isNotEmpty()) {
                    foreach ($spentCoins as $spend) {
                        DB::table('coin_lots')
                            ->where('id', $spend->coin_lot_id)
                            ->increment('remaining', $spend->amount);
                    }
                    DB::table('coin_spends')->where('order_id', $order->id)->delete();
                }

                // Hapus catatan voucher redemption (voucher tidak dikembalikan untuk pembatal)
                if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                    DB::table('voucher_redemptions')->where('order_id', $order->id)->delete();
                }

                // Catat ke status history
                DB::table('order_status_history')->insert([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'dibatalkan',
                    'changed_by' => 'system',
                    'admin_id' => null,
                    'note' => 'Dibatalkan otomatis karena melewati batas pembayaran.',
                    'created_at' => now(),
                ]);

                $canceledCount++;
                $notifyUser = $user;
            });

            // Kirim notifikasi in-app (di luar transaction)
            if ($notifyUser) {
                try {
                    NotificationService::send(
                        $notifyUser,
                        'order_cancelled',
                        "❌ Pesanan #{$order->order_number} dibatalkan",
                        'Kamu tidak menyelesaikan pembayaran dalam batas waktu. Pesanan dibatalkan otomatis. Koin yang kamu pakai sudah dikembalikan.',
                        null,
                        'box',
                    );
                } catch (\Throwable $e) {
                    // ignore — notif opsional
                }
            }
        }

        $this->info("Selesai! Membatalkan {$canceledCount} pesanan expired & menghapus {$deletedSpammers} akun spammer.");

        return self::SUCCESS;
    }
}