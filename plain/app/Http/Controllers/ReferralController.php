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

        $referrals = $user->referralsMade()
            ->with('referee')
            ->paginate(20);

        // Ambil query dasar sekali, clone untuk tiap agregat supaya
        // tidak perlu query baru berulang kali.
        $made = $user->referralsMade();

        // Klik share produk = baris yang punya product_id (bukan legacy klik undang)
        $shareClickQuery = ReferralClick::where('referrer_id', $user->id)
            ->whereNotNull('product_id');
        $shareClicks = (clone $shareClickQuery)->count();
        $rewardPerClick = Setting::integer('referral_reward_click', 10);

        $stats = [
            // ===== Undang teman =====
            'total'        => (clone $made)->count(),
            'rewarded'     => (clone $made)->where('status', Referral::STATUS_REWARDED)->count(),
            'pending'      => (clone $made)->where('status', Referral::STATUS_PENDING)->count(),
            'invite_coins' => (int) (clone $made)
                ->where('status', Referral::STATUS_REWARDED)
                ->sum('referrer_reward'),

            // ===== Share produk =====
            'share_clicks'      => $shareClicks,
            'share_click_coins' => $shareClicks * $rewardPerClick,
        ];

        return view('account.referral', [
            'user'           => $user,
            'referrals'      => $referrals,
            'stats'          => $stats,
            'rewardReferrer' => Setting::integer('referral_reward_referrer', 5000),
            'rewardClick'    => $rewardPerClick,
            'title'          => 'Undang Teman',
        ]);
    }
}
