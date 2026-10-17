<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;

class PurgeOldNotifications extends Command
{
    protected $signature = 'notification:purge';

    protected $description = 'Delete user notifications older than 90 days';

    public function handle(): int
    {
        $deleted = UserNotification::query()
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        $this->info("Purged {$deleted} user notification(s) older than 90 days.");

        return self::SUCCESS;
    }
}
