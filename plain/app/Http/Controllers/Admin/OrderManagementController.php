<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\Order;
use App\Models\Referral;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderManagementController extends Controller
{
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

    // ============================================================
    // BULK ACTION
    // ============================================================
    /**
     * Bulk action untuk banyak order sekaligus.
     *
     * Aksi yang didukung:
     *   - status: ubah status (validasi transisi tetap jalan per-order)
     *
     * Setiap order diproses dalam try/catch TERPISAH supaya:
     *   - satu order yang transisinya tidak sah tidak menggagalkan yang lain
     *   - user dapat ringkasan: "5 berhasil, 2 dilewati"
     *
     * Diproses berurutan (bukan paralel) untuk hindari race condition pada
     * coin_lots / voucher_redemptions.
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1', 'max:200'],
            'ids.*'  => ['integer', 'exists:orders,id'],
            'action' => ['required', 'in:status'],
            // Wajib diisi kalau action = status
            'status' => ['required_if:action,status', Rule::enum(OrderStatus::class)],
        ], [
            'ids.required' => 'Pilih minimal satu pesanan.',
            'ids.max'      => 'Maksimal 200 pesanan per aksi bulk.',
            'status.required_if' => 'Pilih status tujuan.',
        ]);

        $ids       = $validated['ids'];
        $newStatus = $validated['status'];

        $orders = Order::query()
            ->with('user')
            ->whereIn('id', $ids)
            ->get();

        $success = 0;
        $skipped = [];
        $failed  = [];

        foreach ($orders as $order) {
            $oldStatus = $order->status;

            // Skip kalau statusnya sudah sama
            if ($oldStatus === $newStatus) {
                $skipped[] = "{$order->order_number} (sudah {$newStatus})";
                continue;
            }

            // Validasi transisi — pakai rule yang sama dengan single update
            if (! OrderStatus::canTransition($oldStatus, $newStatus)) {
                $skipped[] = "{$order->order_number} ({$oldStatus} → {$newStatus} tidak sah)";
                continue;
            }

            try {
                DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                    $this->applyStatusChange($order, $oldStatus, $newStatus, 'admin');
                });

                AdminLog::record('bulk_update_order_status', $order, [
                    'from' => $oldStatus,
                    'to'   => $newStatus,
                ]);

                // Notifikasi in-app
                if ($order->user) {
                    try {
                        $statusLabel = OrderStatus::tryFrom($newStatus)?->label() ?? $newStatus;
                        NotificationService::orderStatus(
                            $order->user,
                            $order,
                            $statusLabel,
                            "Status pesanan kamu berubah menjadi: {$statusLabel}.",
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim notifikasi bulk status', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }

                // Email (opsional, silent fail)
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

        // Susun pesan hasil
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

    public function show(Order $order)
    {
        $order->load([
            'items.variant.product',
            'paymentProofs.method',
            'user',
            'marketplace',
            'statusHistory.admin',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    // ============================================================
    // UPDATE STATUS ORDER (single)
    // ============================================================
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('status', 'Status tidak berubah.');
        }

        if (! OrderStatus::canTransition($oldStatus, $newStatus)) {
            return back()->withErrors([
                'status' => OrderStatus::transitionErrorMessage($oldStatus, $newStatus),
            ]);
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            $this->applyStatusChange($order, $oldStatus, $newStatus, 'admin');
        });

        AdminLog::record('update_order_status', $order, ['from' => $oldStatus, 'to' => $newStatus]);

        if ($order->user) {
            try {
                $statusLabel = OrderStatus::tryFrom($newStatus)?->label() ?? $newStatus;
                NotificationService::orderStatus(
                    $order->user,
                    $order,
                    $statusLabel,
                    "Status pesanan kamu berubah menjadi: {$statusLabel}.",
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi perubahan status order', [
                    'order_id' => $order->id,
                    'user_id'  => $order->user_id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email update status order', [
                    'order_id'   => $order->id,
                    'user_email' => $order->user->email,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'Status pesanan berhasil diperbarui.');
    }

    // ============================================================
    // CORE: apply perubahan status + efek samping
    // ============================================================
    /**
     * Terapkan transisi status dan semua efek sampingnya.
     *
     * Dipakai oleh updateStatus() (single) & bulk() — supaya logika
     * cashback koin, bonus pertama, reward referral, refund koin, dan
     * refund voucher selalu konsisten apapun entry-point-nya.
     *
     * HARUS dipanggil di dalam DB::transaction.
     */
    private function applyStatusChange(Order $order, string $oldStatus, string $newStatus, string $by): void
    {
        $order->update(['status' => $newStatus]);

        if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
            $order->update(['completed_at' => now()]);
            $expiry = now()->addMonths(Setting::integer('coin_expiry_months', 12));

            // 1) Cashback koin (idempotent)
            $cashbackAlreadyGiven = CoinLot::where('order_id', $order->id)
                ->where('source', 'cashback')
                ->exists();

            if ($order->coin_estimate > 0 && ! $cashbackAlreadyGiven) {
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
                        Log::warning('Gagal kirim notifikasi koin cashback', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            // 2) Bonus koin pertama
            $completedOrdersCount = Order::where('user_id', $order->user_id)
                ->where('status', 'selesai')
                ->count();

            $bonusAlreadyGiven = CoinLot::where('user_id', $order->user_id)
                ->where('source', 'bonus_pertama')
                ->exists();

            if ($completedOrdersCount === 1 && ! $bonusAlreadyGiven) {
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
                        Log::warning('Gagal kirim notifikasi bonus koin pertama', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            // 3) Reward referral (dipanggil terpisah dari blok bonus)
            try {
                $this->rewardReferral($order->user, $expiry);
            } catch (\Throwable $e) {
                Log::error('Gagal memberi reward referral saat order selesai', [
                    'order_id' => $order->id,
                    'user_id'  => $order->user_id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        if ($newStatus === 'dibatalkan') {
            $this->refundCoins($order);
            $this->refundVoucher($order);
            $order->update(['cancelled_at' => $order->cancelled_at ?? now()]);
        }

        $this->recordHistory($order, $oldStatus, $newStatus, $by);
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

    // ============================================================
    // HELPER: refund voucher
    // ============================================================
    private function refundVoucher(Order $order): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
            return;
        }

        DB::table('voucher_redemptions')->where('order_id', $order->id)->delete();
    }

    // ============================================================
    // HELPER: catat riwayat status
    // ============================================================
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

    // ============================================================
    // HELPER: reward referral
    // ============================================================
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
                Log::warning('Gagal kirim notifikasi reward referral (referrer)', [
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
