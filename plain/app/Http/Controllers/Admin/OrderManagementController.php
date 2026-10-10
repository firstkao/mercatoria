<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Referral;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderManagementController extends Controller
{
    /**
     * Daftar semua order + filter status + search.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with(['user', 'paymentProofs'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($u) use ($q) {
                            $u->where('full_name', 'like', "%{$q}%")
                              ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ['all' => Order::count()];
        $statuses = [];

        foreach (OrderStatus::cases() as $case) {
            $counts[$case->value]   = Order::where('status', $case->value)->count();
            $statuses[$case->value] = $case->label();
        }

        return view('admin.orders.index', compact('orders', 'status', 'q', 'counts', 'statuses'));
    }

    /**
     * Bulk action: set status untuk banyak order sekaligus.
     *
     * ⚠️ Cancel/refund TIDAK boleh lewat bulk — harus per-item.
     * Alasan: cancel/refund punya efek samping (refund koin, voucher, dst)
     * yang butuh review individual.
     *
     * Validasi transisi status DIHAPUS — admin bebas pilih status apapun.
     * Cuma cek: order tidak boleh di-set ke status yang sama (skip).
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1', 'max:200'],
            'ids.*'  => ['integer', 'exists:orders,id'],
            'action' => ['required', 'in:status'],
            'status' => ['required_if:action,status', Rule::enum(OrderStatus::class)],
        ], [
            'ids.required'       => 'Pilih minimal satu pesanan.',
            'ids.max'            => 'Maksimal 200 pesanan per aksi bulk.',
            'status.required_if' => 'Pilih status tujuan.',
        ]);

        $ids       = $validated['ids'];
        $newStatus = $validated['status'];

        $orders = Order::query()->with(['user', 'items'])->whereIn('id', $ids)->get();

        $success = 0;
        $skipped = [];
        $failed  = [];

        foreach ($orders as $order) {
            $oldStatus = $order->status;

            if ($oldStatus === $newStatus) {
                $skipped[] = "{$order->order_number} (sudah {$newStatus})";
                continue;
            }

            // Cancel/refund tidak boleh lewat bulk — terlalu berisiko. Harus per-item.
            if (in_array($newStatus, [OrderStatus::Dibatalkan->value, OrderStatus::DanaDikembalikan->value], true)) {
                $skipped[] = "{$order->order_number} (cancel/refund harus per-item)";
                continue;
            }

            // Validasi transisi DIHAPUS — admin bebas pilih status apapun.
            // Kalau butuh balik ke strict mode, aktifkan blok di bawah:
            //
            // if (! OrderStatus::canTransition($oldStatus, $newStatus)) {
            //     $skipped[] = "{$order->order_number} ({$oldStatus} → {$newStatus} tidak sah)";
            //     continue;
            // }

            try {
                DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                    $this->applyGlobalStatus($order, $oldStatus, $newStatus);
                });

                AdminLog::record('bulk_update_order_status', $order, [
                    'from' => $oldStatus,
                    'to'   => $newStatus,
                ]);

                if ($order->user) {
                    try {
                        $label = OrderStatus::tryFrom($newStatus)?->label() ?? $newStatus;
                        NotificationService::orderStatus(
                            $order->user,
                            $order,
                            $label,
                            "Status pesanan kamu berubah menjadi: {$label}."
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim notifikasi bulk status', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }

                if ($order->user?->email) {
                    try {
                        Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim email bulk status', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }

                $success++;
            } catch (\Throwable $e) {
                Log::error('Bulk update status gagal', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                $failed[] = $order->order_number;
            }
        }

        $msg = "{$success} pesanan berhasil diperbarui.";
        if (count($skipped) > 0) {
            $msg .= ' Dilewati: ' . implode(', ', array_slice($skipped, 0, 3))
                  . (count($skipped) > 3 ? ' (+' . (count($skipped) - 3) . ' lainnya)' : '') . '.';
        }
        if (count($failed) > 0) {
            $msg .= ' Gagal: ' . implode(', ', $failed) . '.';
        }

        return back()->with('status', $msg);
    }

    /**
     * Detail satu order.
     */
    public function show(Order $order)
    {
        $order->load([
            'items.variant.product.images',
            'paymentProofs.method',
            'user',
            'marketplace',
            'statusHistory.admin',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    // ============================================================
    // UPDATE STATUS ORDER (GLOBAL)
    // ============================================================
    /**
     * Set semua item ke status baru. Order.status di-set sama.
     *
     * - Untuk status normal: semua item di-reset ke NULL (inherit order.status).
     * - Untuk cancel/refund: cuma boleh kalau SEMUA item belum masuk
     *   "sedang_diproses" (rank < 30). Kalau ada 1 item yang sudah diproses,
     *   admin harus cancel per-item.
     *
     * ✅ Validasi transisi DIHAPUS — admin bebas pilih status apapun untuk
     * koreksi data (mis. salah input, order lama, dsb). Yang penting enum valid.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);

        $newStatus      = $request->status;
        $oldOrderStatus = $order->status;

        if ($oldOrderStatus === $newStatus) {
            return back()->with('status', 'Status tidak berubah.');
        }

        $isCancelOrRefund = in_array($newStatus, [
            OrderStatus::Dibatalkan->value,
            OrderStatus::DanaDikembalikan->value,
        ], true);

        // Guard cancel/refund: cek item yang sudah diproses
        if ($isCancelOrRefund) {
            $processedCount = $order->items->filter(function ($item) use ($order) {
                $eff = $item->item_status ?? $order->status;
                return OrderStatus::rank($eff) >= OrderStatus::cancelLockRank();
            })->count();

            if ($processedCount > 0) {
                return back()->withErrors([
                    'status' => "Tidak bisa dibatalkan/dikembalikan lewat global: {$processedCount} item sudah diproses. " .
                                "Cancel/refund per-item yang masih bisa dibatalkan saja.",
                ]);
            }
        }

        // ✅ Validasi transisi DIHAPUS. Kalau mau balik ke strict mode,
        // aktifkan blok di bawah:
        //
        // if (! $isCancelOrRefund && ! OrderStatus::canTransition($oldOrderStatus, $newStatus)) {
        //     return back()->withErrors([
        //         'status' => OrderStatus::transitionErrorMessage($oldOrderStatus, $newStatus),
        //     ]);
        // }

        DB::transaction(function () use ($order, $oldOrderStatus, $newStatus) {
            $this->applyGlobalStatus($order, $oldOrderStatus, $newStatus);
        });

        AdminLog::record('update_order_status', $order, [
            'from' => $oldOrderStatus,
            'to'   => $newStatus,
        ]);

        if ($order->user) {
            try {
                $label = OrderStatus::tryFrom($newStatus)?->label() ?? $newStatus;
                NotificationService::orderStatus(
                    $order->user,
                    $order,
                    $label,
                    "Status pesanan kamu berubah menjadi: {$label}."
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi perubahan status order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email update status order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'Status pesanan & semua item berhasil diperbarui.');
    }

    // ============================================================
    // UPDATE STATUS PER ITEM
    // ============================================================
    /**
     * Set status per-item lalu recompute order.status = min rank item.
     *
     * Validasi cancel/refund: cuma boleh kalau status efektif item
     * masih < "sedang_diproses" (rank < 30).
     */
    public function updateItemStatus(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        $validated = $request->validate([
            'item_status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);

        $newItemStatus  = $validated['item_status'] ?? null;
        $oldEffective   = $item->item_status ?? $order->status;
        $oldOrderStatus = $order->status;

        // Kalau set ke cancel/refund: cek rank lama
        if (in_array($newItemStatus, [OrderStatus::Dibatalkan->value, OrderStatus::DanaDikembalikan->value], true)) {
            if (OrderStatus::rank($oldEffective) >= OrderStatus::cancelLockRank()) {
                return back()->withErrors([
                    'item_status' => "Item \"{$item->product_name_snapshot}\" sudah diproses, tidak bisa dibatalkan/dikembalikan.",
                ]);
            }
        }

        DB::transaction(function () use ($order, $item, $newItemStatus) {
            $item->update([
                'item_status'            => $newItemStatus,
                'item_status_updated_at' => now(),
            ]);

            $this->recomputeOrderStatus($order);
        });

        $order->refresh();
        $item->refresh();

        $newEffective   = $item->item_status ?? $order->status;
        $newOrderStatus = $order->status;

        if ($oldOrderStatus !== $newOrderStatus) {
            AdminLog::record('update_item_status', $order, [
                'item_id'     => $item->id,
                'item_name'   => $item->product_name_snapshot,
                'item_status' => $newItemStatus,
                'order_from'  => $oldOrderStatus,
                'order_to'    => $newOrderStatus,
            ]);
        }

        if ($oldEffective !== $newEffective && $order->user) {
            try {
                $label = OrderStatus::tryFrom($newEffective)?->label() ?? $newEffective;
                NotificationService::itemStatusChanged($order->user, $order, $item, $label);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi status item', [
                    'order_id' => $order->id,
                    'item_id'  => $item->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return back()->with('status', "Status item \"{$item->product_name_snapshot}\" diperbarui.");
    }

    // ============================================================
    // CORE: apply global status (dipakai updateStatus + bulk)
    // ============================================================
    /**
     * HARUS dipanggil di dalam DB::transaction.
     *
     * Langkah:
     * 1. Reset semua item_status ke NULL (semua inherit).
     * 2. Jalankan efek samping (koin, refund) kalau status berubah ke final/revenue.
     * 3. Set order.status = newStatus.
     * 4. Catat riwayat status.
     */
    private function applyGlobalStatus(Order $order, string $oldStatus, string $newStatus): void
    {
        // 1) Reset semua item ke inherit
        $order->items()->update([
            'item_status'            => null,
            'item_status_updated_at' => now(),
        ]);

        // 2) Efek samping
        $this->applyStatusEffects($order, $oldStatus, $newStatus);

        // 3) Update order.status
        $order->update(['status' => $newStatus]);

        // 4) Timeline
        $this->recordHistory($order, $oldStatus, $newStatus, 'admin');
    }

    // ============================================================
    // CORE: recompute order.status dari status semua item
    // ============================================================
    /**
     * Order.status = status item dengan rank TERKECIL (paling mundur),
     * mengabaikan item yang sudah final (dibatalkan / dana dikembalikan).
     *
     * Khusus: kalau SEMUA item final:
     *   - ada dana_dikembalikan → order = dana_dikembalikan
     *   - selain itu            → order = dibatalkan
     *
     * HARUS dipanggil di dalam DB::transaction.
     */
    private function recomputeOrderStatus(Order $order): void
    {
        $order->loadMissing('items');

        if ($order->items->isEmpty()) {
            return;
        }

        $oldOrderStatus = $order->status;

        $effectiveStatuses = $order->items
            ->map(fn (OrderItem $i) => $i->item_status ?? $order->status)
            ->unique()
            ->values();

        $finalStatuses = OrderStatus::finalStatuses();
        $nonFinal      = $effectiveStatuses->reject(fn ($s) => in_array($s, $finalStatuses, true));

        if ($nonFinal->isEmpty()) {
            // Semua item final → order final juga
            $newOrderStatus = $effectiveStatuses->contains(OrderStatus::DanaDikembalikan->value)
                ? OrderStatus::DanaDikembalikan->value
                : OrderStatus::Dibatalkan->value;
        } else {
            // Min rank dari yang non-final
            $newOrderStatus = $nonFinal
                ->sortBy(fn ($s) => OrderStatus::rank($s))
                ->first();
        }

        if ($oldOrderStatus === $newOrderStatus) {
            return; // gak ada yang berubah
        }

        // Efek samping
        $this->applyStatusEffects($order, $oldOrderStatus, $newOrderStatus);

        // Update
        $order->update(['status' => $newOrderStatus]);

        // Timeline
        $this->recordHistory(
            $order,
            $oldOrderStatus,
            $newOrderStatus,
            'admin',
            'Otomatis dari perubahan status item',
        );
    }

    // ============================================================
    // CORE: efek samping saat order.status berubah
    // ============================================================
    /**
     * Efek samping berjalan otomatis dari status order (bukan dari item).
     *
     * - Selesai            → koin cashback + bonus pertama + reward referral
     * - Dibatalkan         → refund koin + refund voucher + set cancelled_at
     * - DanaDikembalikan   → refund koin + refund voucher
     */
    private function applyStatusEffects(Order $order, string $oldStatus, string $newStatus): void
    {
        // Selesai: koin & bonus (idempotent via cek source)
        if ($newStatus === OrderStatus::Selesai->value && $oldStatus !== OrderStatus::Selesai->value) {
            $order->update(['completed_at' => now()]);
            $expiry = now()->addMonths(Setting::integer('coin_expiry_months', 12));

            // 1) Cashback
            $already = CoinLot::where('order_id', $order->id)->where('source', 'cashback')->exists();
            if ($order->coin_estimate > 0 && ! $already) {
                CoinLot::create([
                    'user_id'    => $order->user_id,
                    'source'     => 'cashback',
                    'order_id'   => $order->id,
                    'amount'     => $order->coin_estimate,
                    'remaining'  => $order->coin_estimate,
                    'earned_at'  => now(),
                    'expires_at' => $expiry,
                ]);

                if ($order->user) {
                    try {
                        NotificationService::coinsEarned($order->user, $order->coin_estimate, 'cashback');
                    } catch (\Throwable $e) {
                        Log::warning('Gagal notif koin cashback', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            // 2) Bonus pertama
            $completed  = Order::where('user_id', $order->user_id)->where('status', 'selesai')->count();
            $bonusGiven = CoinLot::where('user_id', $order->user_id)->where('source', 'bonus_pertama')->exists();
            if ($completed === 1 && ! $bonusGiven) {
                $bonus = Setting::integer('customer_bonus_coin', 1000);
                CoinLot::create([
                    'user_id'    => $order->user_id,
                    'source'     => 'bonus_pertama',
                    'order_id'   => $order->id,
                    'amount'     => $bonus,
                    'remaining'  => $bonus,
                    'earned_at'  => now(),
                    'expires_at' => $expiry,
                ]);

                if ($order->user) {
                    try {
                        NotificationService::coinsEarned($order->user, $bonus, 'bonus_pertama');
                    } catch (\Throwable $e) {
                        Log::warning('Gagal notif bonus koin', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            // 3) Reward referral
            try {
                $this->rewardReferral($order->user, $expiry);
            } catch (\Throwable $e) {
                Log::error('Gagal reward referral', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // Dibatalkan / DanaDikembalikan: refund koin & voucher
        $newIsFinal = in_array($newStatus, [OrderStatus::Dibatalkan->value, OrderStatus::DanaDikembalikan->value], true);
        $oldIsFinal = in_array($oldStatus, [OrderStatus::Dibatalkan->value, OrderStatus::DanaDikembalikan->value], true);

        if ($newIsFinal && ! $oldIsFinal) {
            $this->refundCoins($order);
            $this->refundVoucher($order);

            if ($newStatus === OrderStatus::Dibatalkan->value) {
                $order->update(['cancelled_at' => $order->cancelled_at ?? now()]);
            }
        }
    }

    // ============================================================
    // HELPER: refund koin
    // ============================================================
    private function refundCoins(Order $order): void
    {
        $spends = DB::table('coin_spends')->where('order_id', $order->id)->get();
        $expiryMonths = Setting::integer('coin_expiry_months', 12);

        foreach ($spends as $spend) {
            $lot = DB::table('coin_lots')->where('id', $spend->coin_lot_id)->first();
            $lotExpired = $lot && $lot->expires_at !== null && strtotime($lot->expires_at) <= time();

            if ($lotExpired) {
                CoinLot::create([
                    'user_id'    => $order->user_id,
                    'source'     => 'refund_kedaluwarsa',
                    'order_id'   => $order->id,
                    'amount'     => $spend->amount,
                    'remaining'  => $spend->amount,
                    'earned_at'  => now(),
                    'expires_at' => now()->addMonths($expiryMonths),
                ]);
            } else {
                DB::table('coin_lots')
                    ->where('id', $spend->coin_lot_id)
                    ->increment('remaining', $spend->amount);
            }
        }

        if ($spends->isNotEmpty()) {
            DB::table('coin_spends')->where('order_id', $order->id)->delete();
        }
    }

    private function refundVoucher(Order $order): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
            return;
        }

        DB::table('voucher_redemptions')->where('order_id', $order->id)->delete();
    }

    private function recordHistory(Order $order, ?string $from, string $to, string $by, ?string $note = null): void
    {
        DB::table('order_status_history')->insert([
            'order_id'    => $order->id,
            'from_status' => $from,
            'to_status'   => $to,
            'changed_by'  => $by,
            'admin_id'    => $by === 'admin' ? auth('admin')->id() : null,
            'note'        => $note,
            'created_at'  => now(),
        ]);
    }

    private function rewardReferral(?\App\Models\User $referee, \Illuminate\Support\Carbon $expiry): void
    {
        if (! $referee) {
            return;
        }

        $referral = Referral::query()
            ->where('referee_id', $referee->id)
            ->where('status', Referral::STATUS_PENDING)
            ->first();

        if (! $referral) {
            return;
        }

        $referrerReward = Setting::integer('referral_reward_referrer', 5000);
        $refereeReward  = 0;

        $referral->update([
            'status'          => Referral::STATUS_REWARDED,
            'referrer_reward' => $referrerReward,
            'referee_reward'  => $refereeReward,
            'rewarded_at'     => now(),
        ]);

        $referrer = $referral->referrer;
        if ($referrer && $referrerReward > 0) {
            CoinLot::create([
                'user_id'    => $referrer->id,
                'source'     => 'referral',
                'order_id'   => null,
                'amount'     => $referrerReward,
                'remaining'  => $referrerReward,
                'earned_at'  => now(),
                'expires_at' => $expiry,
            ]);

            try {
                NotificationService::referralRewarded($referrer, $referrerReward, 'referrer');
            } catch (\Throwable $e) {
                Log::warning('Gagal notif reward referral', [
                    'referrer_id' => $referrer->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        AdminLog::record('reward_referral', null, [
            'referrer' => $referrer?->email,
            'referee'  => $referee->email,
            'amount'   => $referrerReward + $refereeReward,
        ]);
    }
}
