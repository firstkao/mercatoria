<?php

namespace App\Console\Commands;

use App\Models\SplitBox;
use App\Models\SplitBoxBatch;
use App\Services\Admin\AdminNotificationService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

    public function handle(SplitBoxService $split, AdminNotificationService $adminNotifications): int
    {
        // ═══ Tahap 1: Auto-open batch baru jika perlu ═══
        $boxes = SplitBox::query()->where('auto_open_batch', true)->get();
        $errors = 0;

        foreach ($boxes as $box) {
            try {
                $split->ensureBatchHealth($box);
            } catch (\Throwable $e) {
                $errors++;
                $this->error("SplitBox #{$box->id}: {$e->getMessage()}");
                Log::error("splitbox:check-batches failed for SplitBox #{$box->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // ═══ Tahap 2: Cek batch yang lewat closes_at ═══
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

        // ═══ Summary ═══
        if ($overdue->isNotEmpty()) {
            $overdue->each(fn ($batch) =>
                $this->warn("Kloter #{$batch->batch_no} (split_box {$batch->split_box_id}) lewat closes_at")
            );
            $this->info("{$overdue->count()} kloter perlu perhatian admin.");
        } else {
            $this->info("Semua kloter dalam kondisi baik.");
        }

        if ($errors > 0) {
            $this->warn("{$errors} split box gagal diproses — cek log.");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
