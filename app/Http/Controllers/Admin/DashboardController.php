<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Spammer;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const NON_REVENUE_STATUSES = [
        OrderStatus::MENUNGGU_PEMBAYARAN,
        OrderStatus::DITAHAN,
        OrderStatus::PEMBAYARAN_GAGAL,
        OrderStatus::DANA_DIKEMBALIKAN,
        OrderStatus::HANGUS,
    ];

    public function __invoke(): View
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $monthStart->copy()->subMonthNoOverflow();
        $weekStart = $now->copy()->startOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $revenueStatuses = $this->revenueStatuses();

        $totalOrders = Order::query()->count();
        $ordersThisMonth = Order::query()->where('created_at', '>=', $monthStart)->count();
        $ordersLastMonth = Order::query()->whereBetween('created_at', [$lastMonthStart, $monthStart])->count();
        $ordersToday = Order::query()->where('created_at', '>=', $now->copy()->startOfDay())->count();
        $ordersYesterday = Order::query()
            ->whereBetween('created_at', [$now->copy()->subDay()->startOfDay(), $now->copy()->startOfDay()])->count();
        $revenueThisMonth = (float) Order::query()->whereIn('status', $revenueStatuses)
            ->where('created_at', '>=', $monthStart)->sum('total');
        $revenueLastMonth = (float) Order::query()->whereIn('status', $revenueStatuses)
            ->whereBetween('created_at', [$lastMonthStart, $monthStart])->sum('total');
        $usersThisWeek = User::query()->where('role', 'customer')->where('created_at', '>=', $weekStart)->count();
        $usersLastWeek = User::query()->where('role', 'customer')
            ->whereBetween('created_at', [$lastWeekStart, $weekStart])->count();

        $stats = [
            [
                'label' => 'Total Pesanan',
                'short' => 'Order',
                'value' => number_format($totalOrders, 0, ',', '.'),
                'compact' => number_format($totalOrders, 0, ',', '.'),
                'change' => $this->percentChange($ordersThisMonth, $ordersLastMonth).' bln ini',
                'trend' => $ordersThisMonth <=> $ordersLastMonth,
            ],
            [
                'label' => 'Pesanan Hari Ini',
                'short' => 'Hari Ini',
                'value' => number_format($ordersToday, 0, ',', '.'),
                'compact' => number_format($ordersToday, 0, ',', '.'),
                'change' => sprintf('%+d dr kemarin', $ordersToday - $ordersYesterday),
                'trend' => $ordersToday <=> $ordersYesterday,
            ],
            [
                'label' => 'Pendapatan Bulan Ini',
                'short' => 'Bln',
                'value' => 'Rp '.number_format($revenueThisMonth, 0, ',', '.'),
                'compact' => number_format($revenueThisMonth / 1_000_000, 1, ',', '.').'jt',
                'change' => $this->percentChange($revenueThisMonth, $revenueLastMonth).' bln ini',
                'trend' => $revenueThisMonth <=> $revenueLastMonth,
            ],
            [
                'label' => 'User Baru Minggu Ini',
                'short' => 'User',
                'value' => number_format($usersThisWeek, 0, ',', '.'),
                'compact' => '+'.$usersThisWeek,
                'change' => $this->percentChange($usersThisWeek, $usersLastWeek).' mgg ini',
                'trend' => $usersThisWeek <=> $usersLastWeek,
            ],
        ];

        $pending = [
            'Bukti Bayar Verif' => [
                Payment::query()->where('status', 'pending')->whereNotNull('proof_path')->count(), 'order',
                route('office.payments.index'),
            ],
            'Refund Pending' => [
                Refund::query()->where('status', 'requested')->count(), 'order',
                route('office.refunds.index'),
            ],
            'Spammer Baru' => [
                Spammer::query()->where('created_at', '>=', $now->copy()->subDays(7))->count(), 'akun',
                route('office.spammers.index'),
            ],
            'Order Baru Masuk' => [
                Order::query()->where('status', OrderStatus::MENUNGGU_PEMBAYARAN->value)->count(), 'order',
                route('office.orders.index'),
            ],
            'Hampir Hangus (7h)' => [
                Order::query()->where('status', OrderStatus::MENUNGGU_PELUNASAN->value)
                    ->where('pickup_deadline_at', '<=', $now->copy()->addDays(2))->count(), 'order',
                route('office.orders.index'),
            ],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $this->salesChart($now, $revenueStatuses),
            'pending' => $pending,
            'topProducts' => $this->topProducts($monthStart, $revenueStatuses),
            'notifications' => AdminNotification::query()->latest()->limit(5)->get(),
            'today' => $now,
        ]);
    }

    /** @return list<string> */
    private function revenueStatuses(): array
    {
        $excluded = array_map(fn (OrderStatus $status): string => $status->value, self::NON_REVENUE_STATUSES);

        return array_values(array_diff(array_map(fn (OrderStatus $status): string => $status->value, OrderStatus::cases()), $excluded));
    }

    private function percentChange(float|int $current, float|int $previous): string
    {
        if ($previous == 0) {
            return $current == 0 ? '0%' : '▲ baru';
        }
        $change = (($current - $previous) / $previous) * 100;

        return sprintf('%+d%%', round($change));
    }

    /** @param list<string> $statuses */
    private function salesChart(CarbonInterface $now, array $statuses): array
    {
        $start = $now->copy()->subDays(29)->startOfDay();
        $totals = Order::query()->whereIn('status', $statuses)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupBy('day')->pluck('revenue', 'day');

        $points = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i);
            $points[] = ['date' => $date, 'value' => (float) ($totals[$date->toDateString()] ?? 0)];
        }

        return $points;
    }

    /** @param list<string> $statuses */
    private function topProducts(CarbonInterface $monthStart, array $statuses): Collection
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $statuses)
            ->where('orders.created_at', '>=', $monthStart)
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.qty) as sold'))
            ->orderByDesc('sold')->limit(5)->get();

        $products = Product::query()->with('games:id,name')->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($products): array {
            $product = $products->get($row->product_id);

            return [
                'name' => $product?->name ?? 'Produk dihapus',
                'game' => $product?->games->first()?->name,
                'sold' => (int) $row->sold,
            ];
        });
    }
}
