<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $until] = $this->range($request);

        $orders = Order::query()
            ->whereBetween('created_at', [$from, $until])
            ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
            ->with('marketplace')
            ->get();

        $totalRevenue = $orders->sum('pay_now_idr');
        $totalOrders = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0;

        $byStatus = $orders->groupBy('status')->map->count()->sortDesc();

        $byMarketplace = $orders
            ->groupBy(fn ($order) => $order->marketplace?->name ?? 'Lainnya')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'revenue' => $group->sum('pay_now_idr'),
            ])
            ->sortByDesc('revenue');

        $dailyRevenue = $this->dailyRevenue($from, $until);
        $monthlyRevenue = $this->monthlyRevenue();
        $statusChart = $this->statusDistribution($from, $until);

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $until])
            ->whereNotIn('orders.status', ['dibatalkan', 'pembayaran_gagal'])
            ->select('order_items.product_name_snapshot', 'order_items.variant_name_snapshot')
            ->selectRaw('SUM(order_items.quantity) as total_qty')
            ->selectRaw('SUM(order_items.line_total_idr) as total_revenue')
            ->groupBy('order_items.product_name_snapshot', 'order_items.variant_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'from' => $from,
            'until' => $until,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'byStatus' => $byStatus,
            'byMarketplace' => $byMarketplace,
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'statusChart' => $statusChart,
            'topProducts' => $topProducts,
        ]);
    }

    public function export(Request $request)
    {
        [$from, $until] = $this->range($request);

        $orders = Order::query()
            ->whereBetween('created_at', [$from, $until])
            ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
            ->with(['user', 'marketplace'])
            ->orderBy('created_at')
            ->get();

        $filename = 'laporan-penjualan-' . $from->format('Ymd') . '-' . $until->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'No. Order', 'Tanggal', 'Pembeli', 'Email', 'Marketplace',
                'Skema', 'Subtotal', 'Diskon', 'Total', 'Dibayar', 'Status',
            ]);

            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->order_number,
                    $order->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
                    $order->user?->full_name ?? '-',
                    $order->user?->email ?? '-',
                    $order->marketplace?->name ?? '-',
                    $order->payment_scheme,
                    $order->subtotal_idr,
                    $order->discount_idr,
                    $order->total_idr,
                    $order->pay_now_idr,
                    $order->status,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $fromInput = $request->query('dari');
        $untilInput = $request->query('sampai');

        $from = $fromInput && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromInput)
            ? Carbon::createFromFormat('Y-m-d', $fromInput, 'Asia/Jakarta')->startOfDay()
            : now('Asia/Jakarta')->startOfMonth()->startOfDay();

        $until = $untilInput && preg_match('/^\d{4}-\d{2}-\d{2}$/', $untilInput)
            ? Carbon::createFromFormat('Y-m-d', $untilInput, 'Asia/Jakarta')->endOfDay()
            : now('Asia/Jakarta')->endOfDay();

        return [$from, $until];
    }

    /**
     * @return array<string, int>
     */
    private function dailyRevenue(Carbon $from, Carbon $until): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$from, $until])
            ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
            ->selectRaw('DATE(created_at) as day, SUM(pay_now_idr) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->day] = (int) $row->total;
        }

        return $out;
    }

    /**
     * Revenue 12 bulan terakhir untuk chart.
     *
     * @return array<int, array{label: string, revenue: int, orders: int}>
     */
    private function monthlyRevenue(): array
    {
        $out = [];
        $start = now('Asia/Jakarta')->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $stats = Order::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotIn('status', ['dibatalkan', 'pembayaran_gagal'])
                ->selectRaw('COALESCE(SUM(pay_now_idr), 0) as revenue, COUNT(*) as orders')
                ->first();

            $out[] = [
                'label' => $month->translatedFormat('M Y'),
                'revenue' => (int) ($stats->revenue ?? 0),
                'orders' => (int) ($stats->orders ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Distribusi order per status untuk chart.
     *
     * @return array<string, int>
     */
    private function statusDistribution(Carbon $from, Carbon $until): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$from, $until])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $label = \App\Enums\OrderStatus::tryFrom($row->status)?->label() ?? $row->status;
            $out[$label] = (int) $row->count;
        }

        return $out;
    }
}