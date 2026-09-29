<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->ensureReferralCode();

        $referrals = $user->referralsMade()
            ->with('referee')
            ->paginate(20);

        $stats = [
            'total' => $user->referralsMade()->count(),
            'rewarded' => $user->referralsMade()->where('status', Referral::STATUS_REWARDED)->count(),
            'pending' => $user->referralsMade()->where('status', Referral::STATUS_PENDING)->count(),
            'coins' => $user->referralsMade()->where('status', Referral::STATUS_REWARDED)->sum('referrer_reward'),
        ];

        return view('account.referral', [
            'user' => $user,
            'referrals' => $referrals,
            'stats' => $stats,
            'rewardReferrer' => Setting::integer('referral_reward_referrer', 5000),
            'rewardReferee' => Setting::integer('referral_reward_referee', 2000),
            'title' => 'Undang Teman',
        ]);
    }
}