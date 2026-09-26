<?php

namespace App\Console\Commands;

use App\Actions\DeleteSpammerAccount;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelUnpaidOrders extends Command
{
    // Menggunakan properti standar agar 100% kompatibel dengan semua versi Laravel
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Membatalkan pesanan yang lewat batas pembayaran dan menghapus akun Spammer';

    public function handle(DeleteSpammerAccount $deleteSpammerAccount): int
    {
        // Mix: Query berbasis waktu dinamis (Kode 2) + Proteksi cek bukti bayar (Kode 1)
        $expiredOrders = Order::with('user')
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<', now())
            ->whereNull('payment_proof_path') // Keamanan ekstra dari Kode 1
            ->get();

        $canceledCount = 0;
        $deletedSpammers = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, $deleteSpammerAccount, &$canceledCount, &$deletedSpammers) {
                $user = $order->user;

                if ($user && method_exists($user, 'isSpammer') && $user->isSpammer()) {
                    // Eksekusi akun Spammer
                    $deleteSpammerAccount->handle($user, 'deleted_unpaid_order');
                    $deletedSpammers++;
                } else {
                    // Customer biasa: Batalkan order
                    $order->update([
                        'status' => 'dibatalkan',
                        'cancelled_at' => now()
                    ]);

                    // Mix: Kembalikan koin yang terpakai (Kode 2)
                    $spentCoins = DB::table('coin_spends')->where('order_id', $order->id)->get();
                    if ($spentCoins->isNotEmpty()) {
                        foreach ($spentCoins as $spend) {
                            DB::table('coin_lots')
                                ->where('id', $spend->coin_lot_id)
                                ->increment('remaining', $spend->amount);
                        }
                        DB::table('coin_spends')->where('order_id', $order->id)->delete();
                    }

                    // Mix: Pengembalian stok yang dirapikan (Kode 1)
                    // TODO: Hilangkan komentar di bawah jika sistemmu memotong stok saat checkout
                    /*
                    foreach ($order->items as $item) {
                        DB::table('product_variants')
                            ->where('id', $item->product_variant_id)
                            ->increment('stock', $item->quantity);
                    }
                    */

                    $canceledCount++;
                }
            });
        }

        // Mix: Output terminal yang lebih rapi
        $this->info("Selesai! Membatalkan {$canceledCount} pesanan expired & menghapus {$deletedSpammers} akun spammer.");

        return self::SUCCESS;
    }
}
