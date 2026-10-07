<?php

namespace App\Http\Controllers;

use App\Models\CoinLot;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Status order yang TIDAK dihitung sebagai "belanja".
     *
     * Order dengan status di bawah ini dianggap tidak jadi transaksi:
     *   - dibatalkan / cancelled : order dibatalkan (baik oleh user, admin, atau sistem)
     *   - refund / refunded      : uang sudah dikembalikan
     *   - dikembalikan / returned: barang dikembalikan
     *   - gagal                  : order gagal permanen
     *
     * Kalau nama status di enum kamu beda, SESUAIKAN list ini. Kalau status di
     * DB tidak ada di list, tidak masalah — whereNotIn akan mengabaikannya.
     */
    private const EXCLUDED_STATUSES = [
        'dibatalkan',
        'cancelled',
        'canceled',
        'refund',
        'refunded',
        'dikembalikan',
        'returned',
        'gagal',
    ];

    public function show(Request $request): View
    {
        $user = $request->user();
        $stats = $this->buildStats($user);

        $unpaidOrders = $user->orders()
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->orderByRaw('COALESCE(payment_deadline_at, created_at) asc')
            ->take(5)
            ->get();

        $recentNotifs = method_exists($user, 'notifications')
            ? $user->notifications()->latest()->take(5)->get()
            : collect();

        $recentOrders = $user->orders()->latest()->take(5)->get();

        return view('account.show', [
            'user'            => $user,
            'orderStats'      => $stats['orderStats'],
            'totalSpent'      => $stats['totalSpent'],
            'activeCoins'     => $stats['activeCoins'],
            'referralCount'   => $stats['referralCount'],
            'viewQuota'       => $stats['viewQuota'],
            'unpaidOrders'    => $unpaidOrders,
            'recentNotifs'    => $recentNotifs,
            'recentOrders'    => $recentOrders,
            'contactWhatsapp' => Setting::get('contact_whatsapp'),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->buildStats($user);

        return response()->json([
            'total_orders'   => number_format($stats['orderStats']['total'] ?? 0, 0, ',', '.'),
            'total_spent'    => PriceCalculator::formatRupiah($stats['totalSpent'] ?? 0),
            'active_coins'   => number_format($stats['activeCoins'] ?? 0, 0, ',', '.'),
            'referral_count' => (int) ($stats['referralCount'] ?? 0),
        ]);
    }

    /**
     * Kumpulkan semua angka dashboard dalam satu tempat supaya:
     *   1. show() dan stats() selalu konsisten (tidak mungkin beda angka)
     *   2. Definisi tiap angka jelas & terkumpul di satu method
     */
    private function buildStats($user): array
    {
        // ============================================================
        // TOTAL PESANAN
        // ============================================================
        // Jumlah order milik user, semua status (termasuk dibatalkan).
        // Alasan: user mungkin mau lihat "berapa kali saya checkout" — jadi
        // order yang dibatalkan pun tetap dihitung sebagai aktivitas.
        $totalOrders = $user->orders()->count();

        // ============================================================
        // TOTAL BELANJA
        // ============================================================
        // Definisi: sum of total_idr dari semua order yang TIDAK dibatalkan
        // /di-refund. Dipilih supaya:
        //   - Order yang masih menunggu pembayaran tetap terhitung
        //     (customer sudah commit checkout, tinggal bayar)
        //   - Order yang benar-benar batal tidak mencemari total
        //
        // Opsi alternatif (kalau mau pakai "uang yang benar-benar sudah dibayar"):
        //   ->sum('pay_now_idr')  // hanya yang sudah keluar dari dompet customer
        // Ini akan menghasilkan angka lebih kecil, karena DP / cicilan yang
        // belum dibayar tidak dihitung.
        $totalSpent = (int) $user->orders()
            ->whereNotIn('status', self::EXCLUDED_STATUSES)
            ->sum('total_idr');

        // ============================================================
        // KOIN AKTIF
        // ============================================================
        // Sisa koin yang belum expired & belum terpakai.
        $activeCoins = (int) CoinLot::where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('remaining');

        // ============================================================
        // UNDANG TEMAN
        // ============================================================
        // Jumlah orang yang sudah pakai kode referral user ini.
        $referralCount = $user->referralsMade()->count();

        // ============================================================
        // VIEW QUOTA (khusus spammer)
        // ============================================================
        $remainingQuota = method_exists($user, 'remainingViewQuota')
            ? $user->remainingViewQuota()
            : null;

        return [
            'orderStats' => [
                'total' => $totalOrders,
            ],
            'totalSpent'    => $totalSpent,
            'activeCoins'   => $activeCoins,
            'referralCount' => $referralCount,
            'viewQuota' => [
                'remaining' => $remainingQuota,
            ],
        ];
    }
}
