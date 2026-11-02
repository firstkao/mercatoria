<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\SplitBoxSlot;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SplitBoxManager extends Component
{
    public Product $product;

    public string $statusMessage = '';

    public bool $statusIsError = false;

    /** @var array<int, string> role per slot id (form state) */
    public array $slotRoles = [];

    /** @var array<int, int> qty_per_box per slot id (form state) */
    public array $slotQtys = [];

    public function mount(Product $product): void
    {
        $this->product = $product->load('splitBox.slots.variant', 'splitBox.batches.batchSlots');
        $this->fillSlotForm();
    }

    public function saveSlot(int $slotId, ?string $role = null, ?int $qtyPerBox = null): void
    {
        $this->authorizeAdmin();

        $role ??= $this->slotRoles[$slotId] ?? 'normal';
        $qtyPerBox ??= (int) ($this->slotQtys[$slotId] ?? 1);

        // Pastikan slot milik produk ini
        $slot = SplitBoxSlot::where('id', $slotId)
            ->whereHas('splitBox', fn ($q) => $q->where('product_id', $this->product->id))
            ->firstOrFail();

        $slot->update([
            'split_role' => in_array($role, ['normal', 'hot_1', 'hot_2'], true) ? $role : 'normal',
            'qty_per_box' => max(1, $qtyPerBox),
        ]);

        $this->statusMessage = 'Slot diperbarui.';

        $this->statusIsError = false;
        $this->refreshData();
    }

    public function openBatch(): void
    {
        $this->authorizeAdmin();

        $splitBox = $this->product->splitBox;

        if (! $splitBox) {
            $this->statusMessage = 'Produk ini bukan split box.';
            $this->statusIsError = true;

            return;
        }

        if ($splitBox->slots->isEmpty()) {
            $this->statusMessage = 'Tambahkan slot terlebih dahulu.';
            $this->statusIsError = true;

            return;
        }

        // Cek apakah masih ada kloter terbuka dengan stok
        $openBatchIds = $splitBox->batches()
            ->where('status', SplitBoxBatch::STATUS_OPEN)
            ->pluck('id');

        if ($openBatchIds->isNotEmpty()) {
            $hasStock = SplitBoxBatchSlot::query()
                ->whereIn('batch_id', $openBatchIds)
                ->whereColumn('qty_taken', '<', 'qty_total')
                ->exists();

            if ($hasStock) {
                $this->statusMessage = 'Masih ada kloter terbuka dengan stok tersedia.';
                $this->statusIsError = true;

                return;
            }
        }

        // Cek limit max_auto_batches
        $totalBatches = $splitBox->batches()->count();

        if ($totalBatches >= $splitBox->max_auto_batches) {
            $this->statusMessage = "Sudah mencapai batas maksimal kloter ({$splitBox->max_auto_batches}).";
            $this->statusIsError = true;

            return;
        }

        $batch = DB::transaction(function () use ($splitBox) {
            $batch = SplitBoxBatch::create([
                'split_box_id' => $splitBox->id,
                'batch_no' => ($splitBox->batches()->max('batch_no') ?? 0) + 1,
                'status' => SplitBoxBatch::STATUS_OPEN,
                'opened_at' => now(),
            ]);

            foreach ($splitBox->slots as $slot) {
                $slot->batchSlots()->create([
                    'batch_id' => $batch->id,
                    'qty_total' => $slot->qty_per_box,
                    'qty_taken' => 0,
                ]);
            }

            return $batch;
        });

        $this->statusMessage = "Kloter #{$batch->batch_no} dibuka.";

        $this->statusIsError = false;
        $this->refreshData();
    }

    /**
     * PRD 49.4: closes_at opsional per kloter (kosong = tanpa batas).
     */
    public function setClosesAt(int $batchId, ?string $closesAt): void
    {
        $this->authorizeAdmin();

        $batch = SplitBoxBatch::where('id', $batchId)
            ->whereHas('splitBox', fn ($q) => $q->where('product_id', $this->product->id))
            ->firstOrFail();

        $batch->update(['closes_at' => filled($closesAt) ? $closesAt : null]);

        $this->statusMessage = 'Batas tutup kloter diperbarui.';
        $this->statusIsError = false;
        $this->refreshData();
    }

    public function closeRefund(int $batchId, SplitBoxService $splitBox): void
    {
        $this->authorizeAdmin();

        // Pastikan batch milik produk ini
        $batch = SplitBoxBatch::where('id', $batchId)
            ->whereHas('splitBox', fn ($q) => $q->where('product_id', $this->product->id))
            ->firstOrFail();

        if (! in_array($batch->status, [SplitBoxBatch::STATUS_OPEN, SplitBoxBatch::STATUS_FULL], true)) {
            $this->statusMessage = 'Kloter ini sudah ditutup.';
            $this->statusIsError = true;

            return;
        }

        $count = $splitBox->closeBatchAndRefund($batch, auth()->user());

        $this->statusMessage = "Kloter ditutup. {$count} permintaan refund dibuat (transfer manual).";

        $this->statusIsError = false;
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.split-box-manager');
    }

    private function refreshData(): void
    {
        $this->product = $this->product->fresh()
            ->load('splitBox.slots.variant', 'splitBox.batches.batchSlots');
        $this->fillSlotForm();
    }

    private function fillSlotForm(): void
    {
        $slots = $this->product->splitBox?->slots ?? collect();
        $this->slotRoles = $slots->pluck('split_role', 'id')->all();
        $this->slotQtys = $slots->pluck('qty_per_box', 'id')->all();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
