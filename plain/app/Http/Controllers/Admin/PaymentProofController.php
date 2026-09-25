<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($proof): void {
            $record = DB::table('payment_proofs')->whereKey($proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            $order = DB::table('orders')->whereKey($record->order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->status === 'menunggu_pembayaran', 422, 'Status order tidak dapat diubah.');

            DB::table('payment_proofs')->whereKey($record->id)->update(['status' => 'approved', 'reviewed_at' => now(), 'updated_at' => now()]);
            DB::table('orders')->whereKey($order->id)->update(['status' => 'pembayaran_diterima', 'paid_at' => now(), 'updated_at' => now()]);
            DB::table('order_status_history')->insert(['order_id' => $order->id, 'from_status' => 'menunggu_pembayaran', 'to_status' => 'pembayaran_diterima', 'changed_by' => 'admin', 'admin_id' => auth('admin')->id(), 'created_at' => now()]);
            DB::table('admin_notifications')->insert(['type' => 'payment_approved', 'order_id' => $order->id, 'created_at' => now()]);

            AdminLog::record('approve_payment', null, ['order_id' => $order->id, 'proof_id' => $record->id]);
        });

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran disetujui dan status order diperbarui.');
    }

    public function reject(Request $request, int $proof): RedirectResponse
    {
        $data = $request->validate(['reject_reason' => ['required', 'string', 'max:1000']]);

        DB::transaction(function () use ($proof, $data): void {
            $record = DB::table('payment_proofs')->whereKey($proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            DB::table('payment_proofs')->whereKey($record->id)->update(['status' => 'rejected', 'reject_reason' => $data['reject_reason'], 'reviewed_at' => now(), 'resubmit_deadline_at' => now()->addHours(24), 'updated_at' => now()]);
            DB::table('admin_notifications')->insert(['type' => 'payment_rejected', 'order_id' => $record->order_id, 'created_at' => now()]);
            AdminLog::record('reject_payment', null, ['order_id' => $record->order_id, 'proof_id' => $record->id, 'reason' => $data['reject_reason']]);
        });

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran ditolak.');
    }
}
