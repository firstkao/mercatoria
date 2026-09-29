<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\Referral;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
    // VERIFY PAYMENT (accept / reject)
    // ============================================================
    public function verifyPayment(Request $request, PaymentProof $proof)
    {
        $request->validate([
            'action' => 'required|in:accept,reject',
            'reject_reason' => 'nullable|string',
        ]);

        $order = $proof->order;
        $oldStatus = $order->status;

        DB::transaction(function () use ($request, $proof, $order, $oldStatus) {
            if ($request->action === 'accept') {
                $proof->update(['status' => 'accepted', 'reviewed_at' => now()]);
                $order->update(['status' => 'pembayaran_diterima', 'paid_at' => now()]);

                // Spammer → Customer
                if ($order->user && $order->user->isSpammer()) {
                    $order->user->update([
                        'role' => 'customer',
                        'became_customer_at' => now(),
                        'view_quota_used' => 0,
                    ]);
                }

                $this->recordHistory($order, $oldStatus, 'pembayaran_diterima', 'admin');
                AdminLog::record('verify_payment_accept', $order, ['order_number' => $order->order_number]);

                // ✅ Notifikasi in-app: pembayaran diterima
                if ($order->user) {
                    try {
                        NotificationService::payment($order->user, $order, 'approved');
                    } catch (\Throwable $e) {
                        // ignore — notifikasi opsional
                    }
                }
            } else {
                $proof->update([
                    'status' => 'rejected',
                    'reject_reason' => $request->reject_reason,
                    'reviewed_at' => now(),
                    'resubmit_deadline_at' => now()->addHours(24),
                ]);
                $order->update(['status' => 'pembayaran_gagal']);

                $this->recordHistory($order, $oldStatus, 'pembayaran_gagal', 'admin', $request->reject_reason);
                AdminLog::record('verify_payment_reject', $order, ['order_number' => $order->order_number]);

                // ✅ Notifikasi in-app: pembayaran ditolak
                if ($order->user) {
                    try {
                        NotificationService::payment($order->user, $order, 'rejected', $request->reject_reason);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        });

        // Email penolakan (di luar transaction biar nggak ganggu kalau SMTP error)
        if ($request->action === 'reject' && $order->user?->email) {
            try {
                Mail::to($order->user->email)
                    ->send(new \App\Mail\PaymentRejected($order, $request->reject_reason ?? ''));
            } catch (\Throwable $e) {
                // ignore — email opsional
            }
        }

        return back()->with('status', 'Verifikasi pembayaran berhasil disimpan.');
    }

    // ============================================================
    // UPDATE STATUS ORDER
    // ============================================================
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|string']);
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

                // 1) Cashback koin
                if ($order->coin_estimate > 0) {
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
                        } catch (\Throwable $e) {}
                    }
                }

                // 2) Bonus koin pertama kali
                $completedOrdersCount = Order::where('user_id', $order->user_id)
                    ->where('status', 'selesai')
                    ->count();

                if ($completedOrdersCount === 1) {
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
                        } catch (\Throwable $e) {}
                    }

                    // ✅ Reward referral (kalau ada)
                    try {
                        $this->rewardReferral($order->user, $expiry);
                    } catch (\Throwable $e) {
                        // ignore — jangan sampai gagal update status
                    }
                }
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
            } catch (\Throwable $e) {}
        }

        // Email update status (opsional)
        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
            } catch (\Throwable $e) {}
        }

        return back()->with('status', 'Status pesanan berhasil diperbarui.');
    }

    // ============================================================
    // HELPER: catat riwayat status
    // ============================================================
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
        $refereeReward = Setting::integer('referral_reward_referee', 2000);

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
            } catch (\Throwable $e) {}
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
            } catch (\Throwable $e) {}
        }

        AdminLog::record('reward_referral', null, [
            'referrer' => $referrer?->email,
            'referee' => $referee->email,
            'amount' => $referrerReward + $refereeReward,
        ]);
    }
}