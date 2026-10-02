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

        // Koin aktif — dibungkus try/catch: kalau tabel coin_lots belum ada di
        // server (migrasi belum jalan), /akun tetap terbuka, bukan 500.
        try {
            $activeCoins = Schema::hasTable('coin_lots')
                ? DB::table('coin_lots')
                    ->where('user_id', $user->id)
                    ->where('remaining', '>', 0)
                    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                    ->sum('remaining')
                : 0;
        } catch (\Throwable) {
            $activeCoins = 0;
        }

        // Wishlist count — fallback 0. Catatan: fitur wishlist belum ada di
        // project ini; view account.show juga tidak memakai variabel ini, tapi
        // tetap dihitung defensif supaya param view tidak pernah Undefined.
        $wishlistCount = 0;
        try {
            if (Schema::hasTable('wishlists')) {
                $wishlistCount = DB::table('wishlists')->where('user_id', $user->id)->count();
            }
        } catch (\Throwable) {
            $wishlistCount = 0;
        }

        // Unread notif — dibungkus try/catch: kalau tabel user_notifications
        // belum ada di server (migrasi belum jalan), /akun tetap terbuka.
        try {
            $unreadNotif = $user->unreadNotificationsCount();
        } catch (\Throwable) {
            $unreadNotif = 0;
        }

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

        // Recent notifications (aman terhadap tabel yang belum ada)
        try {
            $recentNotifs = $user->notifications()
                ->whereNull('read_at')
                ->take(3)
                ->get();
        } catch (\Throwable) {
            $recentNotifs = collect();
        }

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