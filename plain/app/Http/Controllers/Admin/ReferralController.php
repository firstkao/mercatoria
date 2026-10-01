<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinLot;
use App\Models\Referral;
use App\Models\ReferralClick;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'rewarded', 'cancelled'], true)
            ? $request->query('status')
            : null;

        $referrals = Referral::query()
            ->with(['referrer', 'referee'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => Referral::count(),
            'pending' => Referral::where('status', Referral::STATUS_PENDING)->count(),
            'rewarded' => Referral::where('status', Referral::STATUS_REWARDED)->count(),
            'clicks' => ReferralClick::count(),
            'coins_paid' => (int) CoinLot::whereIn('source', ['referral', 'referral_click'])->sum('amount'),
        ];

        return view('admin.referrals.index', [
            'referrals' => $referrals,
            'status' => $status,
            'stats' => $stats,
        ]);
    }
}