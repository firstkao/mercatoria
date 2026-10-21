<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\TierLevel;
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
        $completedReferrals = $user->referrals()->whereNotNull('referee_first_order_completed_at')->count();
        $totalOrders = $user->orders()->count();
        $tier = $user->customer?->tier_enum ?? TierLevel::fromSpending((float) ($user->customer?->total_belanja ?? 0));
        $remainingViews = $user->currentState()->hasViewLimit()
            ? max(0, 10 - $user->productViews()->whereDate('created_at', today())->distinct('product_id')->count('product_id'))
            : null;
        $stateDeadline = match ($user->currentState()->value) {
            'crush' => $user->crush?->expires_at,
            'ex' => $user->customer?->last_order_at?->copy()->addDays(30),
            'ons' => $user->customer?->last_order_at?->copy()->addDays(60),
            default => null,
        };
        $waPhone = preg_replace('/\D/', '', (string) setting('wa_admin_phone', ''));
        $waMessage = $user->currentState()->value === 'blocked'
            ? 'Halo Admin, saya mau minta unblock akun.'
            : 'Halo Admin, saya mau minta reset view produk.';
        $whatsappLink = $waPhone ? 'https://wa.me/'.$waPhone.'?text='.urlencode($waMessage) : null;

        return view('dashboard.index', compact(
            'orders',
            'pendingBills',
            'totalSpent',
            'coinBalance',
            'referralCount',
            'completedReferrals',
            'totalOrders',
            'tier',
            'remainingViews',
            'stateDeadline',
            'whatsappLink',
        ));
    }
}
