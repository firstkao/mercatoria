<?php

namespace App\Console\Commands;

use App\Models\CoinLot;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCoinLifecycle extends Command
{
    protected $signature = 'coins:lifecycle';
    protected $description = 'Menjalankan injeksi koin ulang tahun, peringatan koin hangus, dan pembatalan koin kedaluwarsa';

    public function handle(): int
    {
        $today = now()->timezone('Asia/Jakarta');
        
        $this->info("Menjalankan siklus koin untuk tanggal: " . $today->toDateString());

        DB::transaction(function() use ($today) {
            // 1. Injeksi Koin Ulang Tahun (1000 Koin)
            $birthdayReward = Setting::integer('birthday_coin', 1000);
            $expiryMonths = Setting::integer('coin_expiry_months', 12);
            
            // Cari Customer yang berulang tahun hari ini (mengabaikan tahun lahir)
            $birthdayCustomers = User::where('role', 'customer')
                ->whereMonth('birth_date', $today->month)
                ->whereDay('birth_date', $today->day)
                ->get();

            $birthdayCoinsGiven = 0;
            foreach ($birthdayCustomers as $user) {
                // Pastikan tahun ini belum dapat koin ultah (mencegah double-inject jika cron jalan 2x)
                $alreadyGot = CoinLot::where('user_id', $user->id)
                    ->where('source', 'ulang_tahun')
                    ->where('birthday_year', $today->year)
                    ->exists();

                if (!$alreadyGot) {
                    CoinLot::create([
                        'user_id' => $user->id,
                        'source' => 'ulang_tahun',
                        'birthday_year' => $today->year,
                        'amount' => $birthdayReward,
                        'remaining' => $birthdayReward,
                        'earned_at' => now(),
                        'expires_at' => now()->addMonths($expiryMonths)
                    ]);
                    $birthdayCoinsGiven++;
                    // TODO: Memicu Mailable email notifikasi "Selamat Ulang Tahun" ke $user->email
                }
            }

            // 2. Hanguskan Koin Kedaluwarsa
            $expiredLots = CoinLot::where('remaining', '>', 0)
                ->where('expires_at', '<=', now())
                ->update(['remaining' => 0]);

            // 3. Peringatan H-7 Koin Hangus
            $reminderDays = Setting::integer('coin_expiry_reminder_days', 7);
            $targetDateStart = now()->addDays($reminderDays)->startOfDay();
            $targetDateEnd = now()->addDays($reminderDays)->endOfDay();

            $expiringSoon = CoinLot::with('user')
                ->where('remaining', '>', 0)
                ->whereBetween('expires_at', [$targetDateStart, $targetDateEnd])
                ->get();

            $remindersSent = 0;
            foreach ($expiringSoon as $lot) {
                // TODO: Memicu Mailable email notifikasi koin hampir hangus ke $lot->user->email
                $remindersSent++;
            }

            $this->info("Koin Ultah: $birthdayCoinsGiven akun. Koin Hangus: $expiredLots lot. Email Pengingat: $remindersSent lot.");
        });

        return self::SUCCESS;
    }
}
