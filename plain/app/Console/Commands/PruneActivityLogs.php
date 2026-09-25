<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('activity-logs:prune')]
#[Description('Delete shopper activity older than the retention period (default 12 months)')]
class PruneActivityLogs extends Command
{
    public function handle(): int
    {
        $months = Setting::integer('customer_activity_retention_months', 12);
        $deleted = ActivityLog::query()->where('created_at', '<', now()->subMonths($months))->delete();

        $this->info("Deleted {$deleted} activity log(s) older than {$months} months.");

        return self::SUCCESS;
    }
}