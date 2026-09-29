<?php

namespace App\Console\Commands;

use App\Models\CoinLot;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessCoinLifecycle extends Command
{
    protected $signature = 'coins:lifecycle';
    protected $description = 'Menjalankan injeksi koin ulang tahun, peringatan koin hangus, dan pembatalan koin kedaluwarsa';

    public function handle(): int
    {
        $today = now()->timezone('Asia/Jakarta');

        $this->info("Menjalankan siklus koin untuk tanggal: " . $today->toDateString());

        $birthdayCoinsGiven = $this->injectBirthdayCoins($today);
        $expiredLots = $this->expireOldCoins();
        $remindersSent = $this->sendExpiryReminders();

        $this->info("Koin Ultah: {$birthdayCoinsGiven} akun. Koin Hangus: {$expiredLots} lot. Pengingat: {$remindersSent} notif.");

        return self::SUCCESS;
    }

    /**
     * 1) Koin ulang tahun untuk customer yang ultah hari ini.
     */
    private function injectBirthdayCoins(\Illuminate\Support\Carbon $today): int
    {
        $birthdayReward = Setting::integer('birthday_coin', 1000);
        $expiryMonths = Setting::integer('coin_expiry_months', 12);

        // Skip user yang sudah dianonimkan / tanpa email
        $birthdayCustomers = User::query()
            ->where('role', 'customer')
            ->whereNull('anonymized_at')
            ->whereNotNull('email')
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->get();

        $given = 0;

        foreach ($birthdayCustomers as $user) {
            // Cek sudah dapat tahun ini (anti double-run)
            $alreadyGot = CoinLot::query()
                ->where('user_id', $user->id)
                ->where('source', 'ulang_tahun')
                ->where('birthday_year', $today->year)
                ->exists();

            if ($alreadyGot) {
                continue;
            }

            // Bungkus per user — kalau 1 user error, user lain tetap jalan
            try {
                CoinLot::create([
                    'user_id' => $user->id,
                    'source' => 'ulang_tahun',
                    'birthday_year' => $today->year,
                    'amount' => $birthdayReward,
                    'remaining' => $birthdayReward,
                    'earned_at' => now(),
                    'expires_at' => now()->addMonths($expiryMonths),
                ]);

                // Notifikasi in-app
                try {
                    NotificationService::send(
                        $user,
                        'coins',
                        "🎂 Selamat Ulang Tahun!",
                        "Kamu dapat {$birthdayReward} koin sebagai hadiah ulang tahun. Koin berlaku " .
                        $expiryMonths . " bulan. Selamat berbelanja!",
                        route('account.coins.index'),
                        'wallet',
                    );
                } catch (\Throwable $e) {
                    // notif gagal, koin tetap masuk
                }

                $given++;
            } catch (\Throwable $e) {
                $this->warn("  ⚠️ Gagal inject koin ultah untuk user #{$user->id}: " . $e->getMessage());
            }
        }

        return $given;
    }

    /**
     * 2) Hanguskan koin yang sudah lewat expired.
     */
    private function expireOldCoins(): int
    {
        return CoinLot::query()
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['remaining' => 0]);
    }

    /**
     * 3) Kirim pengingat H-7 ke user yang koinnya mau hangus.
     */
    private function sendExpiryReminders(): int
    {
        $reminderDays = Setting::integer('coin_expiry_reminder_days', 7);

        $targetStart = now()->addDays($reminderDays)->startOfDay();
        $targetEnd = now()->addDays($reminderDays)->endOfDay();

        $expiringSoon = CoinLot::with('user')
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$targetStart, $targetEnd])
            ->get();

        $sent = 0;

        foreach ($expiringSoon as $lot) {
            $user = $lot->user;

            // Skip user null / anonymized
            if (! $user || $user->anonymized_at) {
                continue;
            }

            try {
                NotificationService::send(
                    $user,
                    'coins',
                    "⏰ Koin kamu mau hangus",
                    "Kamu punya {$lot->remaining} koin yang akan hangus pada " .
                    $lot->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y') .
                    ". Pakai sebelum hangus!",
                    route('account.coins.index'),
                    'wallet',
                );

                $sent++;
            } catch (\Throwable $e) {
                $this->warn("  ⚠️ Gagal kirim reminder koin untuk user #{$user->id}: " . $e->getMessage());
            }
        }

        return $sent;
    }
}