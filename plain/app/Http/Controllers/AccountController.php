<?php

namespace App\Http\Controllers;

use App\Models\CoinLot;
use App\Models\Referral;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Dashboard /akun — render stats + ringkasan pesanan/notifikasi.
     *
     * Semua hitungan angka dipindah ke buildStats() supaya endpoint
     * /akun/stats (JSON) memakai logika yang sama persis — tidak mungkin
     * beda angka antara yang di-render server-side & yang di-fetch JS.
     *
     * ⚠️ SESUAIKAN: kalau di file asli kamu ada variabel tambahan (mis.
     * $user->expires_at, $contactWhatsapp, dst.) yang dipakai view, tambahkan
     * di buildStats() atau di compact() di bawah. Struktur di sini mengikuti
     * view account.show yang sekarang sudah pakai: orderStats.total,
     * totalSpent, activeCoins, referralCount, viewQuota, dll.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $stats = $this->buildStats($user);

        // Daftar tagihan yang menunggu pembayaran (dipakai di section
        // "Tagihan Menunggu" — kalau di view kamu ada).
        $unpaidOrders = $user->orders()
            ->whereIn('status', ['menunggu_pembayaran', 'pembayaran_gagal'])
            ->orderByRaw('COALESCE(payment_deadline_at, created_at) asc')
            ->take(5)
            ->get();

        // Notifikasi terbaru (kalau view menampilkannya)
        $recentNotifs = method_exists($user, 'notifications')
            ? $user->notifications()->latest()->take(5)->get()
            : collect();

        // Pesanan terbaru
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

    /**
     * Endpoint JSON untuk live-update stats di dashboard tanpa refresh.
     * Dipanggil JS saat: window focus, tab visibility change, bfcache restore,
     * dan safety-net tiap 60 detik.
     */
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
     * Kumpulkan semua angka dashboard dalam satu tempat.
     *
     * ⚠️ JIKA angka dari file asli kamu beda definisinya (mis. Total Belanja
     * cuma menjumlah order berstatus 'selesai', atau Total Pesanan tidak
     * menghitung order yang dibatalkan), SESUAIKAN query di bawah. Karena
     * angka inilah yang muncul di layar customer.
     */
    private function buildStats($user): array
    {
        // === Total Pesanan ===
        // Semua order milik user, tidak peduli status.
        $totalOrders = $user->orders()->count();

        // === Total Belanja ===
        // Jumlahkan total_idr dari order yang SUDAH DIBAYAR (bukan yang masih
        // menunggu pembayaran). Sesuaikan whereIn-nya kalau kamu punya status
        // berbeda (mis. 'dibayar', 'diproses', dst.).
        $totalSpent = (int) $user->orders()
            ->whereIn('status', [
                'dibayar',
                'diproses',
                'dikirim',
                'selesai',
                'pembayaran_diterima',
                'ditahan', // bukti sudah diupload, menunggu verifikasi — ikut dihitung
            ])
            ->sum('total_idr');

        // === Koin Aktif ===
        // Sisa koin yang belum expired & belum terpakai.
        $activeCoins = (int) CoinLot::where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('remaining');

        // === Undang Teman ===
        // Berapa banyak orang yang pakai kode referral user ini.
        $referralCount = $user->referralsMade()->count();

        // === View Quota (khusus spammer) ===
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
