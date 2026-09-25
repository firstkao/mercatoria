<?php

namespace App\Console\Commands;

use App\Actions\DeleteSpammerAccount;
use App\Models\Order; // Pastikan model Order sudah ada
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('orders:cancel-unpaid')]
#[Description('Membatalkan pesanan yang belum dibayar > 24 jam dan menghapus akun spammer')]
class CancelUnpaidOrders extends Command
{
    public function handle(DeleteSpammerAccount $deleteSpammerAccount): int
    {
        // Cari waktu 24 jam ke belakang
        $expiredTime = now()->subHours(24);

        // Ambil order yang statusnya menunggu pembayaran (sesuaikan field status/bukti bayar dengan tabelmu)
        $expiredOrders = Order::query()
            ->with('user')
            ->whereNull('payment_proof_path') // Asumsi kalau bukti bayar kosong
            ->where('status', 'pending')      // Asumsi status awal pesanan
            ->where('created_at', '<=', $expiredTime)
            ->get();

        $canceledCount = 0;
        $deletedSpammerCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, $deleteSpammerAccount, &$canceledCount, &$deletedSpammerCount) {
                $user = $order->user;

                if ($user && $user->isSpammer()) {
                    // Aturan ketat: Spammer gagal bayar 24 jam -> Akun & order dihapus
                    $deleteSpammerAccount->handle($user, 'deleted_unpaid');
                    $deletedSpammerCount++;
                } else {
                    // Customer biasa: Hanya batalkan order & kembalikan stok
                    $order->update(['status' => 'cancelled']);
                    
                    // TODO: Jika kamu punya mekanisme pemotongan stok saat checkout, kembalikan stoknya di sini
                    // foreach ($order->items as $item) {
                    //     $item->variant()->where('status', 'out_of_stock')->update(['status' => 'available']);
                    // }
                    
                    $canceledCount++;
                }
            });
        }

        $this->info("Selesai! Membatalkan {$canceledCount} pesanan customer & menghapus {$deletedSpammerCount} akun spammer.");

        return self::SUCCESS;
    }
}
