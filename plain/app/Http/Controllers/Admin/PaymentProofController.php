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
            // Setelah customer upload bukti, order berstatus 'ditahan' (lihat OrderController::proof),
            // jadi approve harus menerima 'ditahan' selain 'menunggu_pembayaran'.
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

            DB::table('order_status_history')->insert([
                'order_id' => $orderRow->id,
                'from_status' => $fromStatus,
                'to_status' => 'pembayaran_diterima',
                'changed_by' => 'admin',
                'admin_id' => auth('admin')->id(),
                'created_at' => now(),
            ]);

            // Update user role kalau masih spammer
            $user = DB::table('users')->where('id', $orderRow->user_id)->first();
            if ($user && $user->role === 'spammer') {
                DB::table('users')->where('id', $user->id)->update([
                    'role' => 'customer',
                    'became_customer_at' => now(),
                    'view_quota_used' => 0,
                ]);
            }

            AdminLog::record('approve_payment', null, ['order_id' => $orderRow->id, 'proof_id' => $record->id]);

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
                    // ignore
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

            DB::table('orders')->where('id', $record->order_id)->update([
                'status' => 'pembayaran_gagal',
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
                } catch (\Throwable $e) {}

                if ($user->email) {
                    try {
                        Mail::to($user->email)->send(new \App\Mail\PaymentRejected(Order::find($order->id), $data['reject_reason']));
                    } catch (\Throwable $e) {}
                }
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran ditolak.');
    }
}
