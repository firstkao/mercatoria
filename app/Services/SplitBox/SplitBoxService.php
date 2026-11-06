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
use App\Models\User;
use App\Services\Order\OrderStatusService;
use App\Services\Payment\RefundService;
use App\Services\User\UserNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SplitBoxService
{
    public const CART_PURGED_MESSAGE = 'Maaf, slot karakter di keranjangmu telah terjual ke pembeli lain.';

    /**
     * Validasi modifier: jumlah karakter normal harus cukup untuk memenuhi semua modifier.
     *
     * @param  array<int, array{slot: SplitBoxSlot, qty: int}>  $lines
     */
    public function validatePair(array $lines): bool
    {
        $modifierNeeded = 0;
        $normalCount = 0;

        foreach ($lines as $line) {
            $qty = (int) $line['qty'];
            $modifier = $this->getModifier($line['slot']);

            if ($modifier > 0) {
                $modifierNeeded += $modifier * $qty;
            } else {
                $normalCount += $qty;
            }
        }

        return $normalCount >= $modifierNeeded;
    }

    /**
     * Ambil modifier dari attribute value yang terkait dengan variant slot ini.
     */
    private function getModifier(SplitBoxSlot $slot): int
    {
        $variant = $slot->variant;
        if (! $variant) {
            return 0;
        }

        return (int) $variant->attributeValues()
            ->where('modifier', '>', 0)
            ->max('modifier') ?? 0;
    }

    public function assertOrderPairsValid(Order $order): void
    {
        $items = $order->items()->whereNotNull('split_box_slot_id')->with('splitBoxSlot.variant.attributeValues')->get();

        foreach ($items->groupBy('bundle_group_id') as $bundleItems) {
            $lines = $bundleItems->map(fn (OrderItem $item) => ['slot' => $item->splitBoxSlot, 'qty' => $item->qty])->all();

            if (! $this->validatePair($lines)) {
                throw ValidationException::withMessages([
                    'proof' => 'Syarat Split Box belum terpenuhi (karakter + butuh pasangan normal).',
                ]);
            }
        }
    }

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

    public function lockSlotsForOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $items = $order->items()
                ->whereNotNull('split_box_slot_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return true;
            }

            $allocations = [];
            $reserved = [];

            foreach ($items as $item) {
                $allocation = $this->allocateFifo($item->split_box_slot_id, $item->qty, $reserved);

                if ($allocation === null) {
                    return false;
                }

                $reserved[$allocation[0]->id] = ($reserved[$allocation[0]->id] ?? 0) + $item->qty;
                $allocations[] = [$item, $allocation];
            }

            foreach ($allocations as [$item, $allocation]) {
                [$batchSlot, $batchNo] = $allocation;

                $batchSlot->increment('qty_taken', $item->qty);
                $item->update(['split_box_batch_slot_id' => $batchSlot->id]);
                $this->markBatchFullIfSoldOut($batchSlot->batch);

                $item->snapshot?->update([
                    'split_batch_no' => $batchNo,
                ]);

                $this->cancelUnsatisfiablePendingOrders($batchSlot->slot, $order);
                $this->purgeUnfulfillableCarts($batchSlot->slot, $order->user_id);
            }

            return true;
        });
    }

    public function releaseSlotsForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $items = $order->items()
                ->whereNotNull('split_box_batch_slot_id')
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                $batchSlot = SplitBoxBatchSlot::find($item->split_box_batch_slot_id);

                if ($batchSlot && $batchSlot->qty_taken > 0) {
                    $batchSlot->decrement('qty_taken', min($item->qty, $batchSlot->qty_taken));

                    if ($batchSlot->batch?->status === SplitBoxBatch::STATUS_FULL) {
                        $batchSlot->batch->update(['status' => SplitBoxBatch::STATUS_OPEN]);
                    }
                }

                $item->update(['split_box_batch_slot_id' => null]);
                $item->snapshot?->update(['split_batch_no' => null]);
            }
        });
    }

    public function closeBatchAndRefund(SplitBoxBatch $batch, User $admin): int
    {
        return DB::transaction(function () use ($batch, $admin) {
            if (! in_array($batch->status, [SplitBoxBatch::STATUS_OPEN, SplitBoxBatch::STATUS_FULL], true)) {
                return 0;
            }

            $orderIds = OrderItem::whereIn('split_box_batch_slot_id',
                SplitBoxBatchSlot::where('batch_id', $batch->id)->pluck('id')
            )->pluck('order_id')->unique();

            $batch->update([
                'status' => $orderIds->isEmpty() ? SplitBoxBatch::STATUS_CLOSED : SplitBoxBatch::STATUS_CANCELLED,
            ]);

            $orders = Order::whereIn('id', $orderIds)->lockForUpdate()->get();

            $refunds = app(RefundService::class);
            $count = 0;

            foreach ($orders as $order) {
                $refunds->requestFull($order, 'Slot split box tidak terpenuhi', $admin);
                app(UserNotificationService::class)->notify(
                    $order->user,
                    'split_batch_cancelled',
                    'Kloter Split Box dibatalkan, refund sedang diproses Admin.',
                    '/orders/'.$order->no_order
                );
                $count++;
            }

            return $count;
        });
    }

    private function allocateFifo(int $slotId, int $qty, array $reserved = []): ?array
    {
        $candidates = SplitBoxBatchSlot::query()
            ->where('slot_id', $slotId)
            ->whereHas('batch', fn ($q) => $q->where('status', SplitBoxBatch::STATUS_OPEN))
            ->with('batch')
            ->lockForUpdate()
            ->get()
            ->sortBy(fn (SplitBoxBatchSlot $batchSlot) => $batchSlot->batch->batch_no);

        foreach ($candidates as $batchSlot) {
            if ($batchSlot->availableQty() - ($reserved[$batchSlot->id] ?? 0) >= $qty) {
                return [$batchSlot, $batchSlot->batch->batch_no];
            }
        }

        return null;
    }

    private function markBatchFullIfSoldOut(?SplitBoxBatch $batch): void
    {
        if (! $batch || $batch->status !== SplitBoxBatch::STATUS_OPEN) {
            return;
        }

        $hasStock = SplitBoxBatchSlot::where('batch_id', $batch->id)->whereColumn('qty_taken', '<', 'qty_total')->exists();

        if (! $hasStock) {
            $batch->update(['status' => SplitBoxBatch::STATUS_FULL]);
        }
    }

    private function cancelUnsatisfiablePendingOrders(SplitBoxSlot $slot, Order $trigger): void
    {
        $remaining = $this->remainingStock($slot->id);

        $candidateItems = OrderItem::query()
            ->where('split_box_slot_id', $slot->id)
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::MenungguPembayaran->value))
            ->where('order_id', '!=', $trigger->id)
            ->with('order')
            ->orderBy('created_at')
            ->get();

        $statuses = app(OrderStatusService::class);

        foreach ($candidateItems as $item) {
            if ($remaining >= $item->qty) {
                $remaining -= $item->qty;
                continue;
            }

            $order = $item->order;
            $order->update(['cancel_reason' => Order::CANCEL_SLOT_SOLD]);

            $statuses->changeStatus(
                $order,
                OrderStatus::PembayaranGagal->value,
                'system',
                'slot_sold'
            );
        }
    }

    private function purgeUnfulfillableCarts(SplitBoxSlot $slot, int $exceptUserId): void
    {
        $remaining = $this->remainingStock($slot->id);

        $bundles = Cart::where('variant_id', $slot->variant_id)
            ->where('user_id', '!=', $exceptUserId)
            ->whereNotNull('bundle_group_id')
            ->distinct()
            ->pluck('bundle_group_id');

        foreach ($bundles as $bundleId) {
            $carts = Cart::where('bundle_group_id', $bundleId)->get();

            $needed = $carts->sum(fn ($c) => $c->variant_id === $slot->variant_id ? $c->qty : 0);

            if ($needed > $remaining) {
                $user = $carts->first()->user;
                Cart::where('bundle_group_id', $bundleId)->delete();

                app(UserNotificationService::class)->notify(
                    $user,
                    'split_cart_purged',
                    self::CART_PURGED_MESSAGE,
                    '/cart'
                );
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
