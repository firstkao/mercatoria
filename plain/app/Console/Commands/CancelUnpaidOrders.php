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
            // Bukti yang ditolak memberi customer jendela resubmit (resubmit_deadline_at);
            // jangan batalkan order selama jendela itu masih terbuka.
            ->whereDoesntHave('paymentProofs', fn ($q) => $q
                ->where('status', 'rejected')
                ->where('resubmit_deadline_at', '>', now()))
            ->get();

        $canceledCount = 0;
        $deletedSpammers = 0;

        foreach ($expiredOrders as $order) {
            $oldStatus = $order->status;
            $notifyUser = null;
            $refunded = [];

            DB::transaction(function () use ($order, $oldStatus, $deleteSpammerAccount, &$canceledCount, &$deletedSpammers, &$notifyUser, &$refunded) {
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

                // Bugfix (logika koin): Kembalikan koin yang terpakai. Kalau lot
                // asalnya sudah kedaluwarsa, JANGAN increment ke lot mati — saldo
                // query selalu memfilter expires_at > now(), sehingga koin hasil
                // refund ke lot expired hilang permanen dari pandangan customer.
                // Sebagai gantinya, buat lot baru 'refund_kedaluwarsa' berumur penuh
                // sesuai setting coin_expiry_months.
                $spentCoins = DB::table('coin_spends')->where('order_id', $order->id)->get();
                if ($spentCoins->isNotEmpty()) {
                    $expiryMonths = \App\Models\Setting::integer('coin_expiry_months', 12);
                    foreach ($spentCoins as $spend) {
                        $lot = DB::table('coin_lots')->where('id', $spend->coin_lot_id)->first();
                        $lotExpired = $lot && $lot->expires_at !== null && strtotime($lot->expires_at) <= time();

                        if ($lotExpired) {
                            \App\Models\CoinLot::create([
                                'user_id' => $order->user_id,
                                'source' => 'refund_kedaluwarsa',
                                'order_id' => $order->id,
                                'amount' => $spend->amount,
                                'remaining' => $spend->amount,
                                'earned_at' => now(),
                                'expires_at' => now()->addMonths($expiryMonths),
                            ]);
                        } else {
                            DB::table('coin_lots')
                                ->where('id', $spend->coin_lot_id)
                                ->increment('remaining', $spend->amount);
                        }
                    }
                    DB::table('coin_spends')->where('order_id', $order->id)->delete();
                    $refunded[] = 'koin';
                }

                // Voucher dikembalikan: hapus catatan redemption supaya kuota voucher
                // (usage_limit / per_user_limit) terbuka lagi untuk pemakaian berikutnya.
                if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                    $removed = DB::table('voucher_redemptions')->where('order_id', $order->id)->delete();
                    if ($removed > 0) {
                        $refunded[] = 'voucher';
                    }
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
                        'Kamu tidak menyelesaikan pembayaran dalam batas waktu. Pesanan dibatalkan otomatis.'
                            . ($refunded !== [] ? ' ' . ucfirst(implode(' dan ', $refunded)) . ' yang kamu pakai sudah dikembalikan.' : ''),
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
