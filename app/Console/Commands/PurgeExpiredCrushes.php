<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Crush;
use App\Services\Notification\ScheduledEmailService;
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
                    $wasDeleted = DB::transaction(function () use ($crush): ?array {
                        $locked = Crush::query()->lockForUpdate()->find($crush->id);
                        if (! $locked || ! $locked->verified_at || $locked->verified_at->gt(now()->subDays(30))) {
                            return null;
                        }

                        $user = $locked->user()->lockForUpdate()->first();
                        if (! $user || $user->orders()->exists()) {
                            return null;
                        }

                        $recipient = [$user->email, $user->name];
                        $user->delete();

                        return $recipient;
                    });
                    if ($wasDeleted) {
                        $deleted++;
                        app(ScheduledEmailService::class)->sendToAddress($wasDeleted[0], 'crush_deleted', 'Akun dihapus', [
                            'nama_user' => $wasDeleted[1],
                            'link' => route('home'),
                        ]);
                    }
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
                    app(ScheduledEmailService::class)->send($user, 'state_warning_h7', 'Akun akan dihapus', [
                        'nama_user' => $user->name,
                        'state' => 'Dihapus',
                        'link' => route('home'),
                    ]);
                    $warned++;
                }
            });

        return $warned;
    }
}
