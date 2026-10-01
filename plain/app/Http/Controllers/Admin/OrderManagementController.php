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
        $orders = Order::query()
            ->with(['user', 'paymentProofs'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', compact('orders', 'status'));
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
    // UPDATE STATUS ORDER
    // ============================================================
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('status', 'Status tidak berubah.');
        }

        $coinsGiven = 0;

        DB::transaction(function () use ($order, $oldStatus, $newStatus, &$coinsGiven) {
            $order->update(['status' => $newStatus]);

            if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
                $order->update(['completed_at' => now()]);
                $expiry = now()->addMonths(Setting::integer('coin_expiry_months', 12));

                // 1) Cashback koin (idempotent: hanya sekali per order,
                //    walau status digeser keluar-masuk 'selesai')
                $cashbackAlreadyGiven = CoinLot::where('order_id', $order->id)
                    ->where('source', 'cashback')
                    ->exists();

                if ($order->coin_estimate > 0 && ! $cashbackAlreadyGiven) {
                    CoinLot::create([
                        'user_id' => $order->user_id,
                        'source' => 'cashback',
                        'order_id' => $order->id,
                        'amount' => $order->coin_estimate,
                        'remaining' => $order->coin_estimate,
                        'earned_at' => now(),
                        'expires_at' => $expiry,
                    ]);
                    $coinsGiven += $order->coin_estimate;

                    // ✅ Notifikasi in-app: koin cashback
                    if ($order->user) {
                        try {
                            NotificationService::coinsEarned($order->user, $order->coin_estimate, 'cashback');
                        } catch (\Throwable $e) {
                            Log::warning('Gagal kirim notifikasi koin cashback', [
                                'order_id' => $order->id,
                                'user_id' => $order->user_id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                // 2) Bonus koin pertama kali
                $completedOrdersCount = Order::where('user_id', $order->user_id)
                    ->where('status', 'selesai')
                    ->count();

                $bonusAlreadyGiven = CoinLot::where('user_id', $order->user_id)
                    ->where('source', 'bonus_pertama')
                    ->exists();

                if ($completedOrdersCount === 1 && ! $bonusAlreadyGiven) {
                    $bonus = Setting::integer('customer_bonus_coin', 1000);
                    CoinLot::create([
                        'user_id' => $order->user_id,
                        'source' => 'bonus_pertama',
                        'order_id' => $order->id,
                        'amount' => $bonus,
                        'remaining' => $bonus,
                        'earned_at' => now(),
                        'expires_at' => $expiry,
                    ]);
                    $coinsGiven += $bonus;

                    // ✅ Notifikasi in-app: bonus koin pertama
                    if ($order->user) {
                        try {
                            NotificationService::coinsEarned($order->user, $bonus, 'bonus_pertama');
                        } catch (\Throwable $e) {
                            Log::warning('Gagal kirim notifikasi bonus koin pertama', [
                                'order_id' => $order->id,
                                'user_id' => $order->user_id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // ✅ Reward referral (kalau ada)
                    try {
                        $this->rewardReferral($order->user, $expiry);
                    } catch (\Throwable $e) {
                        // Jangan sampai gagal update status, tapi catat di log.
                        Log::error('Gagal memberi reward referral saat order selesai', [
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Pembatalan manual harus mengembalikan koin, sama seperti pembatalan otomatis.
            if ($newStatus === 'dibatalkan') {
                $this->refundCoins($order);
            }

            $this->recordHistory($order, $oldStatus, $newStatus, 'admin');
        });

        AdminLog::record('update_order_status', $order, ['from' => $oldStatus, 'to' => $newStatus]);

        // ✅ Notifikasi in-app: status order berubah
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
                    'user_id' => $order->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Email update status (opsional)
        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email update status order', [
                    'order_id' => $order->id,
                    'user_email' => $order->user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'Status pesanan berhasil diperbarui.');
    }

    // ============================================================
    // HELPER: catat riwayat status
    // ============================================================
    /**
     * Kembalikan koin yang dipakai order ini ke lot asalnya.
     * Idempotent: baris coin_spends dihapus setelah dikembalikan.
     */
    private function refundCoins(Order $order): void
    {
        $spends = DB::table('coin_spends')->where('order_id', $order->id)->get();

        foreach ($spends as $spend) {
            DB::table('coin_lots')
                ->where('id', $spend->coin_lot_id)
                ->increment('remaining', $spend->amount);
        }

        if ($spends->isNotEmpty()) {
            DB::table('coin_spends')->where('order_id', $order->id)->delete();
        }
    }

    private function recordHistory(Order $order, ?string $from, string $to, string $by, ?string $note = null): void
    {
        DB::table('order_status_history')->insert([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $by,
            'admin_id' => $by === 'admin' ? auth('admin')->id() : null,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    // ============================================================
    // HELPER: reward referral saat referee selesai order pertama
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

        // Kebijakan: referee TIDAK dapat bonus tambahan dari referral.
        // Ia hanya dapat welcome/first-order bonus + cashback transaksi seperti user biasa.
        $refereeReward = 0;

        $referral->update([
            'status' => Referral::STATUS_REWARDED,
            'referrer_reward' => $referrerReward,
            'referee_reward' => $refereeReward,
            'rewarded_at' => now(),
        ]);

        // Reward untuk yang mengundang
        $referrer = $referral->referrer;
        if ($referrer && $referrerReward > 0) {
            CoinLot::create([
                'user_id' => $referrer->id,
                'source' => 'referral',
                'order_id' => null,
                'amount' => $referrerReward,
                'remaining' => $referrerReward,
                'earned_at' => now(),
                'expires_at' => $expiry,
            ]);

            try {
                NotificationService::referralRewarded($referrer, $referrerReward, 'referrer');
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi reward referral (referrer)', [
                    'referrer_id' => $referrer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Reward untuk yang pakai kode
        if ($refereeReward > 0) {
            CoinLot::create([
                'user_id' => $referee->id,
                'source' => 'referral',
                'order_id' => null,
                'amount' => $refereeReward,
                'remaining' => $refereeReward,
                'earned_at' => now(),
                'expires_at' => $expiry,
            ]);

            try {
                NotificationService::referralRewarded($referee, $refereeReward, 'referee');
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi reward referral (referee)', [
                    'referee_id' => $referee->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        AdminLog::record('reward_referral', null, [
            'referrer' => $referrer?->email,
            'referee' => $referee->email,
            'amount' => $referrerReward + $refereeReward,
        ]);
    }
}
