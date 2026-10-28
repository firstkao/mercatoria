<?php

namespace App\Console\Commands;

use App\Models\SplitBoxBatch;
use Illuminate\Console\Command;

class CheckSplitBoxBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'splitbox:check-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek kloter Split Box lewat closes_at yang belum full (PRD 49.14)';

    public function handle(): int
    {
        $overdue = SplitBoxBatch::query()
            ->where('status', SplitBoxBatch::STATUS_OPEN)
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->get();

        foreach ($overdue as $batch) {
            $this->warn("Kloter #{$batch->batch_no} (split_box {$batch->split_box_id}) lewat closes_at — perlu ditutup/di-refund admin.");
        }

        $this->info("{$overdue->count()} kloter perlu perhatian admin.");

        return self::SUCCESS;
    }
}
