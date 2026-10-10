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
    protected $signature = 'carts:send-reminders
                            {--minutes=30 : Jeda antar reminder (menit)}
                            {--dry-run : Tampilkan saja tanpa kirim}';

    protected $description = 'Kirim reminder ke user yang cart-nya nganggur ≥30 menit (repeat sampai checkout / cart kosong)';

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $minutes = (int) ($this->option('minutes') ?: 30);

        if ($minutes < 1) {
            $minutes = 30;
        }

        $cutoff = now()->subMinutes($minutes);

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
        // ============================================================
        $reminders = CartReminder::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('sent_at')
            ->get()
            ->groupBy('user_id');

        $sent = 0;
        $skipped = 0;
        $now  = now();

        foreach ($cartData as $userId => $data) {
            $user = $users->get($userId);
            if (! $user) {
                continue; // user sudah anonim / dihapus
            }

            $lastActivityAt = Carbon::parse($data->last_activity);

            // ------------------------------------------------------------
            // Skip kalau cart baru berubah < 30 menit lalu
            // (timer otomatis reset karena cart_items.updated_at berubah)
            // ------------------------------------------------------------
            if ($lastActivityAt->greaterThan($cutoff)) {
                continue;
            }

            // ------------------------------------------------------------
            // Cari reminder terakhir yang dikirim SETELAH last activity.
            // Reminder yang dikirim sebelum user ubah cart = session lama,
            // tidak dihitung.
            // ------------------------------------------------------------
            $userReminders = $reminders->get($userId, collect());

            $remindersInSession = $userReminders
                ->filter(fn ($r) => $r->sent_at && $r->sent_at->greaterThan($lastActivityAt))
                ->sortByDesc('sent_at');

            // Reminder terakhir di session ini
            $lastReminder = $remindersInSession->first();

            // Kalau udah ada reminder < 30 menit dari sekarang → skip
            if ($lastReminder && $lastReminder->sent_at->greaterThan($cutoff)) {
                $skipped++;
                continue;
            }

            // Nomor reminder berikutnya = jumlah reminder dalam session + 1
            $nextNumber  = $remindersInSession->count() + 1;
            $itemCount   = (int) $data->item_count;
            $idleMinutes = (int) $lastActivityAt->diffInMinutes($now);

            // ------------------------------------------------------------
            // Kirim
            // ------------------------------------------------------------
            if (! $dryRun) {
                try {
                    NotificationService::cartAbandoned($user, $itemCount, $nextNumber);

                    CartReminder::create([
                        'user_id'         => $userId,
                        'reminder_number' => $nextNumber,
                        'item_count'      => $itemCount,
                        'sent_at'         => now(),
                    ]);
                } catch (\Throwable $e) {
                    $this->warn("  ⚠️ Gagal kirim ke {$user->email}: {$e->getMessage()}");
                    continue;
                }
            }

            $this->line("  🛒 Reminder #{$nextNumber} → {$user->email} ({$itemCount} item, idle {$idleMinutes} menit)");
            $sent++;
        }

        $suffix = $skipped > 0 ? " ({$skipped} di-skip, belum 30 menit)" : '';
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Selesai. {$sent} reminder dikirim." . $suffix);

        return self::SUCCESS;
    }
}
