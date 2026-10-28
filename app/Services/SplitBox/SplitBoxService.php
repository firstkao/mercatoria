<?php

namespace App\Services\SplitBox;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\SplitBox;
use App\Models\SplitBoxBatch;
use App\Models\SplitBoxBatchSlot;
use App\Models\SplitBoxSlot;
use App\Services\Order\OrderStatusService;
use Illuminate\Support\Facades\DB;

class SplitBoxService
{
    /**
     * Validasi pair kumulatif (PRD 49.2): jumlah Normal ≥ total kebutuhan pair seluruh Hot.
     * Hot tidak bisa menjadi pasangan Hot (hanya Normal yang dihitung).
     *
     * @param  array<int, array{slot: SplitBoxSlot, qty: int}>  $lines
     */
    public function validatePair(array $lines): bool
    {
        $pairNeeded = 0;
        $normalCount = 0;

        foreach ($lines as $line) {
            $qty = (int) $line['qty'];

            if ($line['slot']->isHot()) {
                $pairNeeded += $line['slot']->pairRequirement() * $qty;
            } else {
                $normalCount += $qty;
            }
        }

        return $normalCount >= $pairNeeded;
    }

    /**
     * PRD 49.5: seluruh karakter dalam satu Split Box memakai price_rmb & weight_gram sama.
     */
    public function validateUniformPricing(Product $product): bool
    {
        $variants = $product->variants()->get();

        if ($variants->count() <= 1) {
            return true;
        }

        $prices = $variants->pluck('price_rmb')->map(fn ($p) => (string) ($p ?? ''))->unique();
        $weights = $variants->pluck('weight_gram')->map(fn ($w) => (string) ($w ?? ''))->unique();

        return $prices->count() === 1 && $weights->count() === 1;
    }

    /**
     * Kunci slot untuk order (PRD 49.6 — atomik seluruh bundle, FIFO lintas kloter).
     * Dipanggil sinkron saat upload bukti bayar, SEBELUM status berubah ke Ditahan.
     *
     * Gagal (slot habis) → order batal otomatis slot_sold (PRD 49.7) → forfeit koin/voucher.
     * Sukses → cek auto-open kloter + batalkan order lain yang tak terpenuhi + purge cart.
     */
    public function lockSlotsForOrder(Order $order): bool
    {
        $items = $order->items()
            ->whereNotNull('split_box_slot_id')
            ->lockForUpdate()
            ->get();

        if ($items->isEmpty()) {
            return true;
        }

        return DB::transaction(function () use ($order, $items) {
            $allocations = [];

            foreach ($items as $item) {
                $allocation = $this->allocateFifo($item->split_box_slot_id, $item->qty);

                if ($allocation === null) {
                    // All-or-nothing: batal total, tidak ada slot terkunci.
                    return false;
                }

                $allocations[] = [$item, $allocation];
            }

            foreach ($allocations as [$item, $allocation]) {
                [$batchSlot, $batchNo] = $allocation;

                $batchSlot->increment('qty_taken', $item->qty);
                $item->update(['split_box_batch_slot_id' => $batchSlot->id]);

                $item->snapshot?->update([
                    'split_role' => $item->splitBoxSlot?->split_role,
                    'split_batch_no' => $batchNo,
                ]);

                $this->autoOpenBatch($batchSlot->slot->splitBox);
                $this->cancelUnsatisfiablePendingOrders($batchSlot->slot, $order);
                $this->purgeUnfulfillableCarts($batchSlot->slot);
            }

            return true;
        });
    }

    /**
     * Lepas slot milik order (PRD 49.6: reject / pembayaran_gagal / dana_dikembalikan).
     * TIDAK dipanggil untuk Hangus (barang sudah dibeli).
     */
    public function releaseSlotsForOrder(Order $order): void
    {
        $items = $order->items()
            ->whereNotNull('split_box_batch_slot_id')
            ->get();

        foreach ($items as $item) {
            $batchSlot = SplitBoxBatchSlot::find($item->split_box_batch_slot_id);

            if ($batchSlot) {
                $batchSlot->decrement('qty_taken', $item->qty);
            }

            $item->update(['split_box_batch_slot_id' => null]);
            $item->snapshot?->update(['split_batch_no' => null]);
        }
    }

    /**
     * Tutup kloter & refund semua order yang terkunci di kloter itu (PRD 49.11).
     */
    public function closeBatchAndRefund(SplitBoxBatch $batch): int
    {
        $batch->update(['status' => SplitBoxBatch::STATUS_CLOSED]);

        $orders = Order::query()
            ->whereIn('id', OrderItem::whereIn('split_box_batch_slot_id',
                SplitBoxBatchSlot::where('batch_id', $batch->id)->pluck('id')
            )->pluck('order_id'))
            ->get();

        $statuses = app(OrderStatusService::class);

        foreach ($orders as $order) {
            $statuses->changeStatus($order, OrderStatus::DanaDikembalikan->value, 'admin', 'Tutup kloter & refund semua');
        }

        return $orders->count();
    }

    /**
     * @return array{0: SplitBoxBatchSlot, 1: int}|null [batch_slot, batch_no]
     */
    private function allocateFifo(int $slotId, int $qty): ?array
    {
        $candidates = SplitBoxBatchSlot::query()
            ->where('slot_id', $slotId)
            ->whereHas('batch', fn ($q) => $q->where('status', SplitBoxBatch::STATUS_OPEN))
            ->orderBy('batch_id')
            ->lockForUpdate()
            ->get();

        foreach ($candidates as $batchSlot) {
            if ($batchSlot->availableQty() >= $qty) {
                return [$batchSlot, $batchSlot->batch->batch_no];
            }
        }

        return null;
    }

    /**
     * Auto-open kloter N+1 (PRD 49.10) bila stok habis / deadlock pair.
     */
    private function autoOpenBatch(SplitBox $splitBox): void
    {
        if (! $splitBox->auto_open_batch) {
            return;
        }

        $openBatches = $splitBox->batches()->where('status', SplitBoxBatch::STATUS_OPEN)->count();

        if ($openBatches > 0) {
            // Masih ada kloter terbuka dengan stok — tidak perlu buka baru.
            $hasStock = SplitBoxBatchSlot::query()
                ->whereIn('batch_id', $splitBox->batches()->where('status', SplitBoxBatch::STATUS_OPEN)->pluck('id'))
                ->get()
                ->contains(fn ($s) => $s->availableQty() > 0);

            if ($hasStock) {
                return;
            }
        }

        $totalBatches = $splitBox->batches()->count();

        if ($totalBatches >= $splitBox->max_auto_batches) {
            return;
        }

        $batch = SplitBoxBatch::create([
            'split_box_id' => $splitBox->id,
            'batch_no' => $totalBatches + 1,
            'status' => SplitBoxBatch::STATUS_OPEN,
            'is_auto_opened' => true,
        ]);

        foreach ($splitBox->slots as $slot) {
            SplitBoxBatchSlot::create([
                'batch_id' => $batch->id,
                'slot_id' => $slot->id,
                'qty_total' => $slot->qty_per_box,
                'qty_taken' => 0,
            ]);
        }
    }

    /**
     * PRD 49.7: order lain berstatus Menunggu Pembayaran yang memuat slot dengan stok
     * tak mencukupi batal otomatis — Pembayaran Gagal + cancel_reason = slot_sold.
     */
    private function cancelUnsatisfiablePendingOrders(SplitBoxSlot $slot, Order $trigger): void
    {
        $remaining = $this->remainingStock($slot->id);

        $candidateItems = OrderItem::query()
            ->where('split_box_slot_id', $slot->id)
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::MenungguPembayaran->value))
            ->where('order_id', '!=', $trigger->id)
            ->with('order')
            ->get();

        $statuses = app(OrderStatusService::class);

        foreach ($candidateItems as $item) {
            if ($remaining >= $item->qty) {
                $remaining -= $item->qty;

                continue;
            }

            $order = $item->order;
            $order->update(['cancel_reason' => Order::CANCEL_SLOT_SOLD]);

            // SLOT_SOLD — pintu forfeit (e) aktif otomatis via changeStatus.
            $statuses->changeStatus(
                $order,
                OrderStatus::PembayaranGagal->value,
                'system',
                'slot_sold'
            );
        }
    }

    /**
     * PRD 49.8: hapus bundle di cart user lain yang tidak lagi terpenuhi.
     */
    private function purgeUnfulfillableCarts(SplitBoxSlot $slot): void
    {
        $remaining = $this->remainingStock($slot->id);

        $bundles = Cart::where('variant_id', $slot->variant_id)
            ->whereNotNull('bundle_group_id')
            ->distinct()
            ->pluck('bundle_group_id');

        foreach ($bundles as $bundleId) {
            $carts = Cart::where('bundle_group_id', $bundleId)->with('variant')->get();
            $needed = $carts->sum(fn ($c) => $c->variant_id === $slot->variant_id ? $c->qty : 0);

            if ($needed > $remaining) {
                Cart::where('bundle_group_id', $bundleId)->delete();
            }
        }
    }

    private function remainingStock(int $slotId): int
    {
        return SplitBoxBatchSlot::query()
            ->where('slot_id', $slotId)
            ->whereHas('batch', fn ($q) => $q->where('status', SplitBoxBatch::STATUS_OPEN))
            ->get()
            ->sum(fn ($s) => $s->availableQty());
    }
}
