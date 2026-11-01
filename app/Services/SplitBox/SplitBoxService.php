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
        return DB::transaction(function () use ($order) {
            // LockForUpdate HARUS di dalam transaction
            $items = $order->items()
                ->whereNotNull('split_box_slot_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return true;
            }

            $allocations = [];

            // Tahap 1: alokasi semua slot (all-or-nothing)
            foreach ($items as $item) {
                $allocation = $this->allocateFifo($item->split_box_slot_id, $item->qty);

                if ($allocation === null) {
                    // Semua atau tidak sama sekali
                    return false;
                }

                $allocations[] = [$item, $allocation];
            }

            // Tahap 2: commit alokasi + side effects
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
        DB::transaction(function () use ($order) {
            $items = $order->items()
                ->whereNotNull('split_box_batch_slot_id')
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                $batchSlot = SplitBoxBatchSlot::find($item->split_box_batch_slot_id);

                if ($batchSlot && $batchSlot->qty_taken > 0) {
                    $batchSlot->decrement('qty_taken', min($item->qty, $batchSlot->qty_taken));
                }

                $item->update(['split_box_batch_slot_id' => null]);
                $item->snapshot?->update(['split_batch_no' => null]);
            }
        });
    }

    /**
     * Tutup kloter & buat permintaan refund full untuk tiap order di kloter (PRD 49.11).
     * Transfer dana manual oleh Admin; koin/voucher kembali saat refund diselesaikan.
     */
    public function closeBatchAndRefund(SplitBoxBatch $batch, User $admin): int
    {
        return DB::transaction(function () use ($batch, $admin) {
            // Guard: jangan double-close
            if ($batch->status !== SplitBoxBatch::STATUS_OPEN) {
                return 0;
            }

            $batch->update(['status' => SplitBoxBatch::STATUS_CLOSED]);

            $orderIds = OrderItem::whereIn('split_box_batch_slot_id',
                    SplitBoxBatchSlot::where('batch_id', $batch->id)->pluck('id')
                )->pluck('order_id')->unique();

            $orders = Order::whereIn('id', $orderIds)->lockForUpdate()->get();

            $refunds = app(RefundService::class);
            $count = 0;

            foreach ($orders as $order) {
                $refunds->requestFull($order, 'Slot split box tidak terpenuhi', $admin);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Pemeliharaan kloter (cron splitbox:check-batches): auto-open kloter baru bila slot habis / deadlock pair.
     */
    public function ensureBatchHealth(SplitBox $splitBox): void
    {
        DB::transaction(function () use ($splitBox) {
            $this->autoOpenBatch($splitBox);
        });
    }

    /**
     * Alokasi FIFO: cari batch slot terbuka dengan stok cukup, urut dari batch_id terkecil.
     *
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

        // Cek apakah masih ada kloter terbuka dengan stok tersisa
        $openBatchIds = $splitBox->batches()
            ->where('status', SplitBoxBatch::STATUS_OPEN)
            ->pluck('id');

        if ($openBatchIds->isNotEmpty()) {
            $hasStock = SplitBoxBatchSlot::query()
                ->whereIn('batch_id', $openBatchIds)
                ->whereColumn('qty_taken', '<', 'qty_total')
                ->exists();

            if ($hasStock) {
                return;
            }
        }

        // Cek limit max_auto_batches
        $totalBatches = $splitBox->batches()->count();

        if ($totalBatches >= $splitBox->max_auto_batches) {
            return;
        }

        // Fresh load slots
        $slots = $splitBox->slots()->get();

        if ($slots->isEmpty()) {
            return;
        }

        // Buat kloter baru
        $batch = SplitBoxBatch::create([
            'split_box_id' => $splitBox->id,
            'batch_no' => ($splitBox->batches()->max('batch_no') ?? 0) + 1,
            'status' => SplitBoxBatch::STATUS_OPEN,
            'is_auto_opened' => true,
        ]);

        // Bulk insert slots untuk kloter baru
        $slotData = $slots->map(fn ($slot) => [
            'batch_id' => $batch->id,
            'slot_id' => $slot->id,
            'qty_total' => $slot->qty_per_box,
            'qty_taken' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        if (! empty($slotData)) {
            SplitBoxBatchSlot::insert($slotData);
        }

        // Notifikasi ke semua admin
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        $notifService = app(UserNotificationService::class);

        foreach ($admins as $admin) {
            $notifService->notify(
                $admin,
                'split_box_opened',
                "Box sudah dibuka — Kloter #{$batch->batch_no} (produk {$splitBox->product_id})",
                '/office/products/' . $splitBox->product_id . '/edit'
            );
        }
    }

    /**
     * PRD 49.7: order lain berstatus Menunggu Pembayaran yang memuat slot dengan stok
     * tak mencukupi batal otomatis — Pembayaran Gagal + cancel_reason = slot_sold.
     */
    private function cancelUnsatisfiablePendingOrders(SplitBoxSlot $slot, Order $trigger): void
    {
        $remaining = $this->remainingStock($slot->id);

        // FIFO: order lama diprioritaskan survive
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

            // SLOT_SOLD — pintu forfeit (e) aktif otomatis via changeStatus
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
            $carts = Cart::where('bundle_group_id', $bundleId)->get();

            $needed = $carts->sum(fn ($c) => $c->variant_id === $slot->variant_id ? $c->qty : 0);

            if ($needed > $remaining) {
                Cart::where('bundle_group_id', $bundleId)->delete();
            }
        }
    }

    /**
     * Hitung stok tersisa untuk slot tertentu di semua kloter terbuka.
     */
    private function remainingStock(int $slotId): int
    {
        return SplitBoxBatchSlot::query()
            ->where('slot_id', $slotId)
            ->whereHas('batch', fn ($q) => $q->where('status', SplitBoxBatch::STATUS_OPEN))
            ->get()
            ->sum(fn ($s) => $s->availableQty());
    }
}
