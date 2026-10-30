<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Spammer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->startOfDay();
        $month = now()->startOfMonth();
        $week = now()->subWeek();

        return view('admin.dashboard', [
            'totalOrders' => Order::count(),
            'todayOrders' => Order::where('created_at', '>=', $today)->count(),
            'monthRevenue' => Payment::where('status', Payment::STATUS_VERIFIED)
                ->where('created_at', '>=', $month)
                ->sum('amount'),
            'newUsers' => User::where('role', '!=', User::ROLE_ADMIN)
                ->where('created_at', '>=', $week)
                ->count(),
            'pendingVerifications' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'newOrders' => Order::where('status', 'menunggu_pembayaran')->count(),
            'topProducts' => Product::orderByDesc('sold_count')->limit(5)->get(['id', 'name', 'sold_count']),
            // DS 14.2: panel "Pesanan Menunggu Aksi" + "5 Notifikasi Admin Terbaru".
            'pesananMenungguAksi' => [
                'verifikasi_bukti' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'refund_pending' => Refund::where('status', Refund::STATUS_REQUESTED)->count(),
                'spammer_baru' => Spammer::where('created_at', '>=', $week)->count(),
                'order_baru' => Order::where('status', 'menunggu_pembayaran')->count(),
                // "Hampir Hangus (7h)": order menunggu pelunasan dengan batas pickup <= 7 jam lagi.
                'hampir_hangus' => Order::where('status', 'menunggu_pelunasan_pengambilan')
                    ->where('pickup_deadline_at', '>=', now())
                    ->where('pickup_deadline_at', '<=', now()->addHours(7))
                    ->count(),
            ],
            'notifikasiTerbaru' => AdminNotification::orderByDesc('id')->limit(5)->get(),
        ]);
    }
}