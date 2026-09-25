<?php

namespace App\Console\Commands;

// use App\Models\Order;
use App\Mail\PaymentReminderMail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

#[Signature('orders:send-reminders')]
#[Description('Mengirim email peringatan untuk pesanan yang belum dibayar mendekati 24 jam')]
class SendPaymentReminders extends Command
{
    public function handle(): int
    {
        // Cari pesanan yang dibuat antara 20 hingga 21 jam yang lalu
        // Kenapa ada range? Supaya email tidak terkirim berkali-kali untuk order yang sama setiap cron jalan.
        $startWindow = now()->subHours(21);
        $endWindow   = now()->subHours(20);

        /* GANTI QUERY INI DENGAN MODEL ORDER KAMU NANTI
        $ordersToRemind = Order::query()
            ->with('user')
            ->whereNull('payment_proof_path')
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startWindow, $endWindow])
            ->get();
        */

        // Asumsi data $ordersToRemind
        $ordersToRemind = []; // Hapus baris ini jika model Order sudah aktif

        $sentCount = 0;

        foreach ($ordersToRemind as $order) {
            $user = $order->user;
            
            if ($user && $user->email) {
                try {
                    // Masukkan ke dalam antrean (Queue)
                    Mail::to($user->email)->queue(new PaymentReminderMail($user /*, $order */));
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email pengingat ke {$user->email}: " . $e->getMessage());
                }
            }
        }

        $this->info("Berhasil menjadwalkan {$sentCount} email pengingat.");

        return self::SUCCESS;
    }
}
