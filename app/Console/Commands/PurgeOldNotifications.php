<?php

namespace App\Console\Commands;

use App\Models\AdminNotification;
use App\Models\UserNotification;
use Illuminate\Console\Command;

class PurgeOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus notifikasi user & admin lebih tua dari 90 hari (PRD 39.7/41.1)';

    public function handle(): int
    {
        $count = UserNotification::where('created_at', '<', now()->subDays(90))->delete();
        $count += AdminNotification::where('created_at', '<', now()->subDays(90))->delete();

        $this->info("{$count} notifikasi dihapus (retensi 90 hari).");

        return self::SUCCESS;
    }
}
