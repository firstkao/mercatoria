<?php

namespace App\Console\Commands;

use App\Models\CartReminder;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendCartReminders extends Command
{
    protected $signature = 'carts:send-reminders {--dry-run : Tampilkan saja tanpa kirim}';
    protected $description = 'Kirim reminder ke user yang cart-nya nganggur (belum checkout)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $reminder1Hours = Setting::integer('cart_reminder_1_hours', 4);
        $reminder2Hours = Setting::integer('cart_reminder_2_hours', 24);
        $maxReminders = 2;

        // ============================================================
        // 1 QUERY: agregat cart per user (last_activity + total qty)
        // ============================================================
        $cartData = DB::table('cart_items')
            ->select(
                'user_id',
                DB::raw('MAX(updated_at) as last_activity'),
                DB::raw('SUM(quantity) as item_count'),
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        if ($cartData->isEmpty()) {
            $this->info('Tidak ada user dengan cart aktif.');
            return self::SUCCESS;
        }

        $userIds = $cartData->keys()->all();

        // ============================================================
        // 1 QUERY: semua user — skip yang sudah dianonimkan / tanpa email
        // ============================================================
        $users = User::query()
            ->whereIn('id', $userIds)
            ->whereNull('anonymized_at')
            ->whereNotNull('email')
            ->get()
            ->keyBy('id');

        // ============================================================
        // 1 QUERY: semua reminder untuk user-user ini
        // (filter per user dilakukan di memory)
        // ============================================================
        $reminders = CartReminder::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('sent_at')
            ->get()
            ->groupBy('user_id');

        $sent = 0;
        $now = now();

        foreach ($cartData as $userId => $data) {
            $user = $users->get($userId);
            if (! $user) {
                continue; // user sudah anonim / dihapus
            }

            $lastActivityAt = Carbon::parse($data->last_activity);
            $hoursIdle = (int) $lastActivityAt->diffInHours($now);

            // Skip kalau belum cukup idle
            if ($hoursIdle < $reminder1Hours) {
                continue;
            }

            // Hitung reminder yang sudah dikirim SETELAH last activity cart
            $userReminders = $reminders->get($userId, collect());
            $sentAfterActivity = $userReminders->filter(
                fn ($r) => $r->sent_at && $r->sent_at->greaterThan($lastActivityAt)
            );

            $remindersInSession = $sentAfterActivity->count();

            if ($remindersInSession >= $maxReminders) {
                continue;
            }

            // Tentukan nomor reminder berikutnya
            $nextNumber = $remindersInSession + 1;

            // Kalau user sudah idle >= batas reminder #2 & belum pernah dapat reminder apa pun,
            // langsung kirim #2 (skip #1) — hindari double-notif dalam 1-2 jam
            if ($remindersInSession === 0 && $hoursIdle >= $reminder2Hours) {
                $nextNumber = 2;
            }

            // Kalau mau kirim #2 tapi belum cukup idle → skip
            if ($nextNumber === 2 && $hoursIdle < $reminder2Hours) {
                continue;
            }

            $itemCount = (int) $data->item_count;

            if (! $dryRun) {
                try {
                    NotificationService::cartAbandoned($user, $itemCount, $nextNumber);

                    CartReminder::create([
                        'user_id' => $userId,
                        'reminder_number' => $nextNumber,
                        'item_count' => $itemCount,
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    $this->warn("  ⚠️ Gagal kirim ke {$user->email}: {$e->getMessage()}");
                    continue;
                }
            }

            $this->line("  🛒 Reminder #{$nextNumber} → {$user->email} ({$itemCount} item, idle {$hoursIdle}h)");
            $sent++;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Selesai. {$sent} reminder dikirim.");

        return self::SUCCESS;
    }
}