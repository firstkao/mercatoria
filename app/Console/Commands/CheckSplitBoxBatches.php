<?php

namespace App\Console\Commands;

use App\Models\SplitBoxBatch;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Console\Command;

class CheckSplitBoxBatches extends Command
{
    protected $signature = 'splitbox:check-batches';

    protected $description = 'Cek kloter Split Box yang lewat closes_at';

    public function handle(AdminNotificationService $adminNotifications): int
    {
        $overdue = SplitBoxBatch::query()
            ->where('status', SplitBoxBatch::STATUS_OPEN)
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->get();

        foreach ($overdue as $batch) {
            $adminNotifications->notify(
                'split_batch_expired',
                "Kloter #{$batch->batch_no} SplitBox #{$batch->split_box_id} terbuka melewati batas isi. Tutup + refund atau perpanjang closes_at.",
                "/office/split-boxes/{$batch->split_box_id}/batches"
            );
        }

        if ($overdue->isNotEmpty()) {
            $overdue->each(fn ($batch) => $this->warn("Kloter #{$batch->batch_no} (split_box {$batch->split_box_id}) lewat closes_at"));
            $this->info("{$overdue->count()} kloter perlu perhatian admin.");
        } else {
            $this->info('Semua kloter dalam kondisi baik.');
        }

        return self::SUCCESS;
    }
}
