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
        // ✅ BUG FIX: dulu pakai window created_at 20–21 jam. Itu salah untuk dua
        // kasus: (a) order DP — pengingat malah dikirim ~21 jam setelah order
        // dibuat padahal deadline bayar SISA 21 jam (harusnya saat deadline
        // MENDEKAT); (b) order yang statusnya sudah digeser admin ke
        // pembayaran_gagal tapi deadline-nya masih jauh — tidak pernah diingatkan.
        // Sekarang berbasis payment_deadline_at: kirim saat tersisa 3–4 jam,
        // dengan window 1 jam agar tidak berulang (scheduler jalan tiap jam).
        $deadlineStart = now()->addHours(3);
        $deadlineEnd = now()->addHours(4);

        $ordersToRemind = Order::query()
            ->with('user')
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->whereBetween('payment_deadline_at', [$deadlineStart, $deadlineEnd])
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