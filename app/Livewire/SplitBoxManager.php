<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxSlot;
use App\Services\SplitBox\SplitBoxService;
use Livewire\Component;

class SplitBoxManager extends Component
{
    public Product $product;

    public string $statusMessage = '';

    public function mount(Product $product): void
    {
        $this->product = $product->load('splitBox.slots.variant', 'splitBox.batches.batchSlots');
    }

    public function saveSlot(int $slotId, string $role, int $qtyPerBox): void
    {
        $slot = SplitBoxSlot::findOrFail($slotId);
        $slot->update([
            'split_role' => in_array($role, ['normal', 'hot_1', 'hot_2'], true) ? $role : 'normal',
            'qty_per_box' => max(1, $qtyPerBox),
        ]);

        $this->statusMessage = 'Slot diperbarui.';
        $this->mount($this->product);
    }

    public function openBatch(): void
    {
        $splitBox = $this->product->splitBox;

        if (! $splitBox) {
            return;
        }

        $batch = SplitBoxBatch::create([
            'split_box_id' => $splitBox->id,
            'batch_no' => $splitBox->batches()->count() + 1,
            'status' => SplitBoxBatch::STATUS_OPEN,
        ]);

        foreach ($splitBox->slots as $slot) {
            $slot->batchSlots()->create([
                'batch_id' => $batch->id,
                'qty_total' => $slot->qty_per_box,
                'qty_taken' => 0,
            ]);
        }

        $this->statusMessage = "Kloter #{$batch->batch_no} dibuka.";
        $this->mount($this->product);
    }

    public function closeRefund(int $batchId, SplitBoxService $splitBox): void
    {
        $batch = SplitBoxBatch::findOrFail($batchId);
        $count = $splitBox->closeBatchAndRefund($batch, auth()->user());

        $this->statusMessage = "Kloter ditutup. {$count} order di-refund.";
        $this->mount($this->product);
    }

    public function render()
    {
        return view('livewire.split-box-manager');
    }
}
