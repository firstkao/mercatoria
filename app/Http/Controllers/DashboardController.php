<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders()->with(['items.snapshot', 'payments'])->latest()->paginate(10);
        $pendingBills = $user->orders()
            ->with('payments')
            ->whereIn('status', [
                OrderStatus::MENUNGGU_PEMBAYARAN->value,
                OrderStatus::MENUNGGU_PELUNASAN->value,
            ])
            ->latest()
            ->get();
        $totalSpent = $user->orders()
            ->whereIn('status', [OrderStatus::SELESAI->value, OrderStatus::MENUNGGU_PELUNASAN->value])
            ->sum('total');
        $coinBalance = (float) ($user->coin?->balance ?? 0);
        $referralCount = $user->referrals()->count();
        $totalOrders = $user->orders()->count();

        return view('dashboard.index', compact(
            'orders',
            'pendingBills',
            'totalSpent',
            'coinBalance',
            'referralCount',
            'totalOrders',
        ));
    }
}
