<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Models\SplitBoxBatch;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Str;
use Livewire\Component;

class SplitBoxPicker extends Component
{
    public Product $product;

    /** @var array<int, int> slot_id => qty terpilih */
    public array $selected = [];

    public string $message = '';

    public bool $messageIsError = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['splitBox.slots.variant', 'splitBox.slots.batchSlots.batch']);

        foreach ($this->product->splitBox?->slots ?? [] as $slot) {
            $this->selected[$slot->id] = 0;
        }
    }

    public function increment(int $slotId): void
    {
        $available = $this->getAvailable($slotId);
        $this->selected[$slotId] = min($available, ($this->selected[$slotId] ?? 0) + 1);
        $this->message = '';
        $this->messageIsError = false;
    }

    public function decrement(int $slotId): void
    {
        $this->selected[$slotId] = max(0, ($this->selected[$slotId] ?? 0) - 1);
        $this->message = '';
        $this->messageIsError = false;
    }

    public function addToCart(SplitBoxService $splitBox): void
    {
        if (! $this->product->splitBox) {
            $this->message = 'Produk ini bukan split box.';
            $this->messageIsError = true;

            return;
        }

        $lines = [];

        foreach ($this->product->splitBox->slots as $slot) {
            $qty = (int) ($this->selected[$slot->id] ?? 0);

            if ($qty > 0) {
                // Cek stok tersedia
                $available = $this->getAvailable($slot->id);

                if ($qty > $available) {
                    $this->message = "Slot \"{$slot->variant?->name}\" tersisa {$available}. Kurangi jumlahnya.";
                    $this->messageIsError = true;

                    return;
                }

                $lines[] = ['slot' => $slot, 'qty' => $qty];
            }
        }

        if ($lines === []) {
            $this->message = 'Pilih minimal 1 karakter.';
            $this->messageIsError = true;

            return;
        }

        if (! $splitBox->validatePair($lines)) {
            $this->message = 'Kamu memilih karakter Hot. Pilih cukup karakter Normal untuk memenuhi syarat pair.';
            $this->messageIsError = true;

            return;
        }

        $bundleId = (string) Str::uuid();

        foreach ($lines as $line) {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product->id,
                'variant_id' => $line['slot']->variant_id,
                'split_box_slot_id' => $line['slot']->id,
                'bundle_group_id' => $bundleId,
                'qty' => $line['qty'],
            ]);
        }

        $this->message = 'Paket Split Box masuk keranjang.';
        $this->messageIsError = false;
        $this->selected = array_fill_keys(array_keys($this->selected), 0);
    }

    public function render()
    {
        $this->product->load(['splitBox.slots.variant', 'splitBox.slots.batchSlots.batch']);

        $hotCount = 0;
        $pairNeeded = 0;
        $normalCount = 0;

        foreach ($this->product->splitBox?->slots ?? [] as $slot) {
            $qty = (int) ($this->selected[$slot->id] ?? 0);

            if ($slot->isHot()) {
                $hotCount += $qty;
                $pairNeeded += $slot->pairRequirement() * $qty;
            } else {
                $normalCount += $qty;
            }
        }

        return view('livewire.split-box-picker', [
            'hotCount' => $hotCount,
            'pairMissing' => max(0, $pairNeeded - $normalCount),
            'slots' => $this->product->splitBox?->slots->map(fn ($slot) => [
                'id' => $slot->id,
                'name' => $slot->variant?->name,
                'role' => $slot->split_role,
                'available' => $slot->batchSlots
                    ->filter(fn ($bs) => $bs->batch->status === SplitBoxBatch::STATUS_OPEN)
                    ->sum(fn ($bs) => $bs->availableQty()),
            ]) ?? [],
        ]);
    }

    /**
     * Hitung stok tersedia untuk slot (hanya batch terbuka).
     */
    private function getAvailable(int $slotId): int
    {
        $slot = $this->product->splitBox?->slots->firstWhere('id', $slotId);

        if (! $slot) {
            return 0;
        }

        // Satu item dialokasikan utuh ke satu kloter (FIFO), jadi batas per baris = stok kloter terbesar.
        return (int) $slot->batchSlots
            ->filter(fn ($bs) => $bs->batch->status === SplitBoxBatch::STATUS_OPEN)
            ->max(fn ($bs) => $bs->availableQty());
    }
}
