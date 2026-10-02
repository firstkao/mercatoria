<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        // Statistik order
        $orderStats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'selesai' => Order::where('user_id', $user->id)->where('status', 'selesai')->count(),
            'proses' => Order::where('user_id', $user->id)
                ->whereIn('status', ['pembayaran_diterima', 'sedang_diproses', 'sampai_wh_cn', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia'])
                ->count(),
            'pending' => Order::where('user_id', $user->id)
                ->whereIn('status', ['menunggu_pembayaran', 'ditahan', 'pembayaran_gagal'])
                ->count(),
        ];

        // Total belanja
        $totalSpent = Order::where('user_id', $user->id)
            ->whereIn('status', ['pembayaran_diterima', 'sedang_diproses', 'sampai_wh_cn', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'selesai'])
            ->sum('pay_now_idr');

        // Koin aktif
        $activeCoins = DB::table('coin_lots')
            ->where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('remaining');

        // Unread notif
        $unreadNotif = $user->unreadNotificationsCount();

        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // FASE 1 (lanjutan): "Tagihan Menunggu" dipindah ke dashboard akun,
        // karena menu 'Pesanan Saya' tidak lagi muncul di navbar kiri.
        $unpaidOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->orderByRaw('COALESCE(payment_deadline_at, created_at) asc')
            ->take(5)
            ->get();

        // Recent notifications
        $recentNotifs = $user->notifications()
            ->whereNull('read_at')
            ->take(3)
            ->get();

        return view('account.show', [
            'user' => $user,
            'viewQuota' => Setting::integer('view_quota', 10),
            'orderStats' => $orderStats,
            'totalSpent' => (int) $totalSpent,
            'activeCoins' => (int) $activeCoins,
            'wishlistCount' => $wishlistCount,
            'unreadNotif' => $unreadNotif,
            'recentOrders' => $recentOrders,
            'unpaidOrders' => $unpaidOrders,
            'recentNotifs' => $recentNotifs,
            'title' => 'Dashboard Saya',
        ]);
    }
}