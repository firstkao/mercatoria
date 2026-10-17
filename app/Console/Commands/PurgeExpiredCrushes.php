<?php

namespace App\Console\Commands;

use App\Models\Crush;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredCrushes extends Command
{
    protected $signature = 'user:purge-crush';

    protected $description = 'Permanently delete verified Crush accounts after 30 days without an order';

    public function handle(): int
    {
        $deleted = 0;
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

        $this->info("Permanently deleted {$deleted} expired Crush account(s) without orders.");

        return self::SUCCESS;
    }
}
