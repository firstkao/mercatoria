<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
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
            'coins_paid' => (int) Referral::where('status', Referral::STATUS_REWARDED)
                ->selectRaw('SUM(referrer_reward + referee_reward) as total')
                ->value('total'),
        ];

        return view('admin.referrals.index', [
            'referrals' => $referrals,
            'status' => $status,
            'stats' => $stats,
        ]);
    }
}