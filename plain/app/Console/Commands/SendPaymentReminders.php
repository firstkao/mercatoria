<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

#[Signature('orders:send-reminders')]
#[Description('Mengirim email peringatan untuk pesanan yang belum dibayar mendekati 24 jam')]
class SendPaymentReminders extends Command
{
    public function handle(): int
    {
        // Window 20-21 jam untuk mencegah pengiriman berulang
        $startWindow = now()->subHours(21);
        $endWindow = now()->subHours(20);

        $ordersToRemind = Order::query()
            ->with('user')
            ->where('status', 'menunggu_pembayaran')
            ->whereBetween('created_at', [$startWindow, $endWindow])
            ->whereDoesntHave('paymentProofs', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->get();

        $sentCount = 0;

        foreach ($ordersToRemind as $order) {
            $user = $order->user;

            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->queue(new PaymentReminderMail($user, $order));
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email pengingat ke {$user->email}: " . $e->getMessage());
                }
            }
        }

        $this->info("Berhasil menjadwalkan {$sentCount} email pengingat.");

        return self::SUCCESS;
    }
}