<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Str;
use Livewire\Component;

class SplitBoxPicker extends Component
{
    public Product $product;

    /** @var array<int, int> slot_id => qty terpilih */
    public array $selected = [];

    public string $message = '';

    public function mount(Product $product): void
    {
        $this->product = $product;

        foreach ($product->splitBox?->slots ?? [] as $slot) {
            $this->selected[$slot->id] = 0;
        }
    }

    public function increment(int $slotId): void
    {
        $this->selected[$slotId] = min(99, ($this->selected[$slotId] ?? 0) + 1);
        $this->message = '';
    }

    public function decrement(int $slotId): void
    {
        $this->selected[$slotId] = max(0, ($this->selected[$slotId] ?? 0) - 1);
        $this->message = '';
    }

    public function addToCart(SplitBoxService $splitBox): void
    {
        $lines = [];

        foreach ($this->product->splitBox->slots as $slot) {
            $qty = (int) ($this->selected[$slot->id] ?? 0);

            if ($qty > 0) {
                $lines[] = ['slot' => $slot, 'qty' => $qty];
            }
        }

        if ($lines === []) {
            $this->message = 'Pilih minimal 1 karakter.';

            return;
        }

        if (! $splitBox->validatePair($lines)) {
            $this->message = 'Kamu memilih karakter Hot. Pilih cukup karakter Normal untuk memenuhi syarat pair.';

            return;
        }

        $bundleId = (string) Str::uuid();

        foreach ($lines as $line) {
            Cart::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'product_id' => $this->product->id,
                    'variant_id' => $line['slot']->variant_id,
                    'bundle_group_id' => $bundleId,
                ],
                ['qty' => $line['qty']]
            );
        }

        $this->message = 'Paket Split Box masuk keranjang.';
        $this->selected = array_fill_keys(array_keys($this->selected), 0);
    }

    public function render()
    {
        return view('livewire.split-box-picker', [
            'slots' => $this->product->splitBox?->slots->map(fn ($slot) => [
                'id' => $slot->id,
                'name' => $slot->variant?->name,
                'role' => $slot->split_role,
                'available' => $slot->batchSlots->sum(fn ($bs) => max(0, $bs->qty_total - $bs->qty_taken)),
            ]) ?? [],
        ]);
    }
}
