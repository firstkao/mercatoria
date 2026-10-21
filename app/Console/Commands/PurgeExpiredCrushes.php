<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Crush;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredCrushes extends Command
{
    protected $signature = 'user:purge-crush';

    protected $description = 'Permanently delete verified Crush accounts after 30 days without an order';

    public function handle(): int
    {
        $deleted = 0;
        $warned = $this->warnCrushesNearDeletion();
        Crush::query()
            ->whereNotNull('verified_at')
            ->where('verified_at', '<=', now()->subDays(30))
            ->orderBy('id')
            ->chunkById(100, function ($crushes) use (&$deleted): void {
                foreach ($crushes as $crush) {
                    $wasDeleted = DB::transaction(function () use ($crush): bool {
                        $locked = Crush::query()->lockForUpdate()->find($crush->id);
                        if (! $locked || ! $locked->verified_at || $locked->verified_at->gt(now()->subDays(30))) {
                            return false;
                        }

                        $user = $locked->user()->lockForUpdate()->first();
                        if (! $user || $user->orders()->exists()) {
                            return false;
                        }

                        $user->delete();

                        return true;
                    });
                    $deleted += (int) $wasDeleted;
                }
            });

        $this->info("Permanently deleted {$deleted} expired Crush account(s) without orders. Warned {$warned} Crush account(s).");

        return self::SUCCESS;
    }

    /**
     * H-7 sebelum auto-delete (hari ke-23 sejak verifikasi), satu kali per akun.
     */
    private function warnCrushesNearDeletion(): int
    {
        $warned = 0;
        Crush::query()
            ->whereNotNull('verified_at')
            ->where('verified_at', '<=', now()->subDays(23))
            ->where('verified_at', '>', now()->subDays(30))
            ->with('user')
            ->orderBy('id')
            ->chunkById(100, function ($crushes) use (&$warned): void {
                foreach ($crushes as $crush) {
                    $user = $crush->user;
                    if (! $user || $user->orders()->exists()
                        || $user->notifications()->where('subtype', 'state_warning_delete')->exists()) {
                        continue;
                    }

                    UserNotificationService::push(
                        $user->id,
                        NotificationType::STATE,
                        'Akun akan dihapus 7 hari lagi',
                        'Akun kamu akan dihapus 7 hari lagi. Yuk order sebelum hilang!',
                        route('home'),
                        subtype: 'state_warning_delete',
                    );
                    $warned++;
                }
            });

        return $warned;
    }
}
