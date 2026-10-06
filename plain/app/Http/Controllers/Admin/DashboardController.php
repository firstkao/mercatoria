<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Support\PriceCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // Stats utama
        $stats = [
            // Grup Katalog
            'Produk tayang' => Product::query()->published()->count(),
            'Draf produk' => Product::query()->where('is_published', false)->count(),

            // Grup Pengguna
            'Spammer' => User::query()->where('role', UserRole::Spammer)->count(),
            'Customer' => User::query()->where('role', UserRole::Customer)->count(),
            'Pendaftar reseller baru' => DB::table('reseller_applications')->where('status', 'pending')->count(),

            // Grup Penjualan
            'Pesanan baru' => DB::table('orders')
                ->whereNotIn('status', ['selesai', 'dibatalkan', 'dana_dikembalikan'])
                ->count(),
            'Bukti pembayaran pending' => DB::table('payment_proofs')->where('status', 'pending')->count(),
        ];

        // Revenue 7 hari terakhir
        $dailyLabels = [];
        $dailyRevenue = [];
        $dailyOrders = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now('Asia/Jakarta')->subDays($i)->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $dailyLabels[] = $day->translatedFormat('D, j M');

            $data = DB::table('orders')
                ->whereBetween('created_at', [$day->copy()->utc(), $dayEnd->copy()->utc()])
                ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
                ->selectRaw(OrderStatus::revenueSumSql().' as revenue, COUNT(*) as orders')
                ->first();

            $dailyRevenue[] = (int) ($data->revenue ?? 0);
            $dailyOrders[] = (int) ($data->orders ?? 0);
        }

        // Summary hari ini
        $todayStart = now('Asia/Jakarta')->startOfDay();
        $todayData = DB::table('orders')
            ->where('created_at', '>=', $todayStart->copy()->utc())
            ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
            ->selectRaw(OrderStatus::revenueSumSql().' as revenue, COUNT(*) as orders')
            ->first();

        // Ringkasan mingguan
        $weekStart = now('Asia/Jakarta')->startOfWeek();
        $weekData = DB::table('orders')
            ->where('created_at', '>=', $weekStart->copy()->utc())
            ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
            ->selectRaw(OrderStatus::revenueSumSql().' as revenue, COUNT(*) as orders')
            ->first();

        // Top 5 produk terlaris (30 hari)
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->whereIn('orders.status', OrderStatus::revenueValues())
            ->select('order_items.product_name_snapshot')
            ->selectRaw('SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'ratesConfigured' => PriceCalculator::fromSettings()->isConfigured(),
            'dailyLabels' => $dailyLabels,
            'dailyRevenue' => $dailyRevenue,
            'dailyOrders' => $dailyOrders,
            'todayRevenue' => (int) ($todayData->revenue ?? 0),
            'todayOrders' => (int) ($todayData->orders ?? 0),
            'weekRevenue' => (int) ($weekData->revenue ?? 0),
            'weekOrders' => (int) ($weekData->orders ?? 0),
            'topProducts' => $topProducts,
        ]);
    }
}
