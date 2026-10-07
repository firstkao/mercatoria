<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->ensureReferralCode();

        // ===== Statistik Undang Teman (referral user) =====
        $referrals = $user->referralsMade()
            ->with('referee')
            ->paginate(20);

        $referralsMade = $user->referralsMade();

        $stats = [
            // Undang teman
            'total'     => (clone $referralsMade)->count(),
            'rewarded'  => (clone $referralsMade)->where('status', Referral::STATUS_REWARDED)->count(),
            'pending'   => (clone $referralsMade)->where('status', Referral::STATUS_PENDING)->count(),
            'invite_coins' => (int) (clone $referralsMade)
                ->where('status', Referral::STATUS_REWARDED)
                ->sum('referrer_reward'),

            // Share link produk
            'share_clicks' => ReferralClick::where('referrer_id', $user->id)
                ->whereNotNull('product_id')
                ->count(),
            'share_click_coins' => (int) Setting::integer('referral_reward_click', 10)
                * ReferralClick::where('referrer_id', $user->id)
                    ->whereNotNull('product_id')
                    ->count(),
        ];

        return view('account.referral', [
            'user'           => $user,
            'referrals'      => $referrals,
            'stats'          => $stats,
            'rewardReferrer' => Setting::integer('referral_reward_referrer', 5000),
            'rewardClick'    => Setting::integer('referral_reward_click', 10),
            'title'          => 'Undang Teman',
        ]);
    }
}
