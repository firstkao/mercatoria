<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $orders = Order::query()
            ->with('user', 'paymentProofs')
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        $order->load('items.variant.product', 'paymentProofs.method', 'user', 'marketplace');
        return view('admin.orders.show', compact('order'));
    }

    public function verifyPayment(Request $request, PaymentProof $proof)
    {
        $request->validate(['action' => 'required|in:accept,reject', 'reject_reason' => 'nullable|string']);
        $order = $proof->order;

        DB::transaction(function() use ($request, $proof, $order) {
            if ($request->action === 'accept') {
                $proof->update(['status' => 'accepted', 'reviewed_at' => now()]);
                $order->update(['status' => 'pembayaran_diterima', 'paid_at' => now()]);
                
                // Ubah Spammer jadi Customer
                if ($order->user->isSpammer()) {
                    $order->user->update([
                        'role' => 'customer',
                        'became_customer_at' => now(),
                        'view_quota_used' => 0
                    ]);
                }
                AdminLog::record('verify_payment_accept', $order, ['order_number' => $order->order_number]);
            } else {
                $proof->update([
                    'status' => 'rejected', 
                    'reject_reason' => $request->reject_reason, 
                    'reviewed_at' => now(),
                    'resubmit_deadline_at' => now()->addHours(24) // Beri waktu 24 jam baru
                ]);
                $order->update(['status' => 'pembayaran_gagal']);
                AdminLog::record('verify_payment_reject', $order, ['order_number' => $order->order_number]);
            }
        });

        // TODO: Memicu antrean pengiriman Email notifikasi ke pelanggan di sini

        return back()->with('status', 'Verifikasi pembayaran berhasil disimpan.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|string']);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::transaction(function() use ($order, $oldStatus, $newStatus) {
            $order->update(['status' => $newStatus]);
            
            // Logika Koin saat Selesai
            if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
                $order->update(['completed_at' => now()]);
                $expiry = now()->addMonths(Setting::integer('coin_expiry_months', 12));

                // 1. Koin Cashback 1%
                if ($order->coin_estimate > 0) {
                    CoinLot::create([
                        'user_id' => $order->user_id,
                        'source' => 'cashback',
                        'order_id' => $order->id,
                        'amount' => $order->coin_estimate,
                        'remaining' => $order->coin_estimate,
                        'earned_at' => now(),
                        'expires_at' => $expiry
                    ]);
                }

                // 2. Bonus Koin Pertama (Jika ini order selesai pertamanya)
                $completedOrdersCount = Order::where('user_id', $order->user_id)->where('status', 'selesai')->count();
                if ($completedOrdersCount === 1) {
                    $bonus = Setting::integer('customer_bonus_coin', 1000);
                    CoinLot::create([
                        'user_id' => $order->user_id,
                        'source' => 'bonus_pertama',
                        'order_id' => $order->id,
                        'amount' => $bonus,
                        'remaining' => $bonus,
                        'earned_at' => now(),
                        'expires_at' => $expiry
                    ]);
                }
            }

            // Catat ke History
            DB::table('order_status_history')->insert([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => 'admin',
                'admin_id' => auth('admin')->id(),
                'created_at' => now()
            ]);
        });

        AdminLog::record('update_order_status', $order, ['from' => $oldStatus, 'to' => $newStatus]);
        return back()->with('status', 'Status pesanan berhasil diperbarui.');
    }
}
