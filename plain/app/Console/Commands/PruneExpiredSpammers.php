<?php

namespace App\Console\Commands;

use App\Actions\DeleteSpammerAccount;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('spammers:prune-expired')]
#[Description('Delete spammer accounts whose 30-day window has passed')]
class PruneExpiredSpammers extends Command
{
    public function handle(DeleteSpammerAccount $deleteSpammerAccount): int
    {
        $deleted = 0;

        User::query()
            ->where('role', UserRole::Spammer)
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($users) use ($deleteSpammerAccount, &$deleted): void {
                foreach ($users as $user) {
                    $deleteSpammerAccount->handle($user, 'deleted_expired');
                    $deleted++;
                }
            });

        $this->info("Deleted {$deleted} expired spammer account(s).");

        return self::SUCCESS;
    }
}