<?php

namespace App\Console\Commands;

use App\Services\Order\LatePenaltyService;
use Illuminate\Console\Command;

class ExpirePenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order dengan denda yang lewat batas bayar kembali ke Hangus (PRD 51.1)';

    public function handle(LatePenaltyService $penalties): int
    {
        $count = $penalties->expireOverdue();

        $this->info("{$count} order kembali ke Hangus (denda tidak dibayar).");

        return self::SUCCESS;
    }
}
