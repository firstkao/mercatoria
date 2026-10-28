<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use Illuminate\Console\Command;

class PurgeRetainedLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus activity/auth/admin log yang lebih tua dari 30 hari (PRD 39.7)';

    public function handle(): int
    {
        $threshold = now()->subDays((int) setting('log_retention_days', 30));

        $deleted = 0;
        $deleted += ActivityLog::where('created_at', '<', $threshold)->delete();
        $deleted += AuthLog::where('created_at', '<', $threshold)->delete();
        $deleted += AdminLog::where('created_at', '<', $threshold)->delete();

        $this->info("{$deleted} baris log dihapus (retensi {$threshold->toDateString()}).");

        return self::SUCCESS;
    }
}
