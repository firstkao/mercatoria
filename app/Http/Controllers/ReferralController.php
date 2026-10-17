<?php

namespace App\Http\Controllers;

use App\Enums\CoinSource;
use App\Models\ShareClick;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $referrals = $user->referrals()->with('referee')->latest('id')->paginate(30);
        $shareClicks = ShareClick::query()->where('user_id', $user->id);

        return view('referrals.index', [
            'referrals' => $referrals,
            'totalReferrals' => $user->referrals()->count(),
            'completedReferrals' => $user->referrals()->whereNotNull('referee_first_order_completed_at')->count(),
            'pendingReferrals' => $user->referrals()->whereNull('referee_first_order_completed_at')->count(),
            'referralCoins' => $user->coinTransactions()->where('source', CoinSource::REFERRER_REWARD->value)->sum('amount'),
            'shareCount' => (clone $shareClicks)->count(),
            'shareCoins' => $user->coinTransactions()->where('source', CoinSource::SHARE->value)->sum('amount'),
            'inviteUrl' => $user->referral_code ? route('register', ['ref' => $user->referral_code]) : null,
        ]);
    }
}
