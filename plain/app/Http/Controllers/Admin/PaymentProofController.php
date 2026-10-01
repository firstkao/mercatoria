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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected', 'all'], true)
            ? $request->query('status')
            : 'pending';

        $proofs = DB::table('payment_proofs')
            ->join('orders', 'orders.id', '=', 'payment_proofs.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('payment_methods', 'payment_methods.id', '=', 'payment_proofs.payment_method_id')
            ->when($status !== 'all', fn ($query) => $query->where('payment_proofs.status', $status))
            ->select('payment_proofs.id', 'payment_proofs.status', 'payment_proofs.amount_idr', 'payment_proofs.uploaded_at', 'orders.order_number', 'orders.pay_now_idr', 'orders.status as order_status', 'users.full_name', 'users.email', 'payment_methods.label as payment_method_label')
            ->latest('payment_proofs.uploaded_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', compact('proofs', 'status'));
    }

    public function show(int $proof): View
    {
        $proofRecord = DB::table('payment_proofs')
            ->join('orders', 'orders.id', '=', 'payment_proofs.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('payment_methods', 'payment_methods.id', '=', 'payment_proofs.payment_method_id')
            ->where('payment_proofs.id', $proof)
            ->select('payment_proofs.*', 'orders.order_number', 'orders.pay_now_idr', 'orders.status as order_status', 'users.full_name', 'users.email', 'payment_methods.label as payment_method_label')
            ->firstOrFail();

        return view('admin.payments.show', [
            'proof' => $proofRecord,
            'imageUrl' => Storage::disk('public')->url($proofRecord->proof_path),
        ]);
    }

    public function approve(int $proof): RedirectResponse
    {
        $order = null;

        DB::transaction(function () use ($proof, &$order): void {
            $record = DB::table('payment_proofs')->where('id', $proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            $orderRow = DB::table('orders')->where('id', $record->order_id)->lockForUpdate()->firstOrFail();

            abort_unless(
                in_array($orderRow->status, ['menunggu_pembayaran', 'ditahan'], true),
                422,
                'Status order tidak dapat diubah.'
            );

            $fromStatus = $orderRow->status;

            DB::table('payment_proofs')->where('id', $record->id)->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('orders')->where('id', $orderRow->id)->update([
                'status' => 'pembayaran_diterima',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

            // ✅ BUG FIX: Catat history dua langkah (ditahan → pembayaran_diterima
            // melewati satu status). Tanpa ini, timeline customer bolong: order
            // tiba-tiba "lompat" dari Menunggu Pembayaran ke Pembayaran Diterima.
            if ($fromStatus === 'ditahan') {
                DB::table('order_status_history')->insert([
                    'order_id' => $orderRow->id,
                    'from_status' => 'menunggu_pembayaran',
                    'to_status' => 'ditahan',
                    // ✅ 'user' (bukan 'customer'): konvensi changed_by di codebase
                    // ini adalah admin|user|system — lihat timeline.blade.php dan
                    // OrderManagementController::recordHistory(). Kalau 'customer',
                    // atribusi "oleh pembeli" tidak muncul di timeline.
                    'changed_by' => 'user',
                    'admin_id' => null,
                    'note' => 'Bukti pembayaran diunggah.',
                    'created_at' => $record->uploaded_at ?? now(),
                ]);
            }

            DB::table('order_status_history')->insert([
                'order_id' => $orderRow->id,
                'from_status' => $fromStatus,
                'to_status' => 'pembayaran_diterima',
                'changed_by' => 'admin',
                'admin_id' => auth('admin')->id(),
                'created_at' => now(),
            ]);

            // ✅ Update user role kalau masih spammer
            $user = DB::table('users')->where('id', $orderRow->user_id)->lockForUpdate()->first();
            if ($user && $user->role === 'spammer') {
                DB::table('users')->where('id', $user->id)->update([
                    'role' => 'customer',
                    'became_customer_at' => now(),
                    'view_quota_used' => 0,
                    'updated_at' => now(),
                ]);
            }

            // ✅ BUG FIX: Bagikan koin ke user (sebelumnya tidak ada!)
            if ($orderRow->coin_estimate > 0) {
                $expiryMonths = Setting::integer('coin_expiry_months', 12);

                CoinLot::create([
                    'user_id' => $orderRow->user_id,
                    'order_id' => $orderRow->id,
                    'amount' => $orderRow->coin_estimate,
                    'remaining' => $orderRow->coin_estimate,
                    'source' => 'purchase',
                    'earned_at' => now(),
                    'expires_at' => now()->addMonths($expiryMonths),
                ]);
            }

            // ✅ TODO: Logika referral (jika ada)
            // if ($user && $user->referral_code) {
            //     $referral = Referral::where('code', $user->referral_code)->first();
            //     if ($referral) {
            //         // Berikan komisi ke referrer
            //     }
            // }

            AdminLog::record('approve_payment', null, [
                'order_id' => $orderRow->id,
                'proof_id' => $record->id,
                'coin_awarded' => $orderRow->coin_estimate,
            ]);

            $order = $orderRow;
        });

        // Notifikasi in-app di luar transaction
        if ($order) {
            $user = \App\Models\User::find($order->user_id);
            if ($user) {
                try {
                    $orderModel = Order::find($order->id);
                    NotificationService::payment($user, $orderModel, 'approved');
                } catch (\Throwable $e) {
                    // ✅ Rapikan: jangan telan diam-diam — approval tetap sukses,
                    // tapi kegagalan notifikasi dicatat agar bisa ditelusuri.
                    Log::warning('Gagal kirim notifikasi pembayaran disetujui', [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran disetujui dan status order diperbarui.');
    }

    public function reject(Request $request, int $proof): RedirectResponse
    {
        $data = $request->validate(['reject_reason' => ['required', 'string', 'max:1000']]);
        $order = null;

        DB::transaction(function () use ($proof, $data, &$order): void {
            $record = DB::table('payment_proofs')->where('id', $proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            DB::table('payment_proofs')->where('id', $record->id)->update([
                'status' => 'rejected',
                'reject_reason' => $data['reject_reason'],
                'reviewed_at' => now(),
                'resubmit_deadline_at' => now()->addHours(24),
                'updated_at' => now(),
            ]);

            $currentOrder = DB::table('orders')->where('id', $record->order_id)->lockForUpdate()->firstOrFail();

            // ✅ BUG FIX: Reset payment_deadline_at. Order dg status 'ditahan'
            // TIDAK dicek oleh scheduler pembatalan (CancelUnpaidOrders hanya
            // memindai menunggu_pembayaran/pembayaran_gagal). Selama ini saat
            // reject, order langsung bisa dibatalkan otomatis padahal email
            // notifikasi menjanjikan "jendela resubmit 24 jam" — karena
            // payment_deadline_at lama sudah lewat. Jendela resubmit di proof
            // (resubmit_deadline_at) dan di order kini disamakan: now + 24 jam.
            $newDeadline = now()->addHours(24);

            DB::table('orders')->where('id', $record->order_id)->update([
                'status' => 'pembayaran_gagal',
                'payment_deadline_at' => $newDeadline,
                'updated_at' => now(),
            ]);

            DB::table('order_status_history')->insert([
                'order_id' => $record->order_id,
                'from_status' => $currentOrder->status,
                'to_status' => 'pembayaran_gagal',
                'changed_by' => 'admin',
                'admin_id' => auth('admin')->id(),
                'note' => $data['reject_reason'],
                'created_at' => now(),
            ]);

            AdminLog::record('reject_payment', null, [
                'order_id' => $record->order_id,
                'proof_id' => $record->id,
                'reason' => $data['reject_reason'],
            ]);

            $order = DB::table('orders')->where('id', $record->order_id)->first();
        });

        // Notifikasi in-app + email di luar transaction
        if ($order) {
            $user = \App\Models\User::find($order->user_id);

            if ($user) {
                try {
                    $orderModel = Order::find($order->id);
                    NotificationService::payment($user, $orderModel, 'rejected', $data['reject_reason']);
                } catch (\Throwable $e) {
                    // ✅ Rapikan: catat kegagalan notifikasi (bukan ditelan diam-diam)
                    Log::warning('Gagal kirim notifikasi pembayaran ditolak', [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                if ($user->email) {
                    try {
                        Mail::to($user->email)->send(new \App\Mail\PaymentRejected(Order::find($order->id), $data['reject_reason']));
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim email penolakan pembayaran', [
                            'order_id' => $order->id,
                            'user_email' => $user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran ditolak.');
    }
}
