<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use Illuminate\Console\Command;

class PurgeRetainedLogs extends Command
{
    protected $signature = 'log:purge';

    protected $description = 'Delete activity, authentication, and admin logs older than 30 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);
        $deleted = [
            'activity' => ActivityLog::query()->where('created_at', '<', $cutoff)->delete(),
            'auth' => AuthLog::query()->where('created_at', '<', $cutoff)->delete(),
            'admin' => AdminLog::query()->where('created_at', '<', $cutoff)->delete(),
        ];

        $this->info(sprintf(
            'Purged logs: activity=%d, auth=%d, admin=%d.',
            $deleted['activity'],
            $deleted['auth'],
            $deleted['admin'],
        ));

        return self::SUCCESS;
    }
}
