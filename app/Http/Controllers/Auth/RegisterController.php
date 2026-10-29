<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->userData();

        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->input('referral_code'))->first();
        } else {
            // PRD 12: atribusi cookie 30 hari dari klik link referral/share.
            $payload = json_decode((string) $request->cookie('referral_attribution'), true);
            $setAt = is_array($payload) ? ($payload['set_at'] ?? null) : null;

            if (! empty($payload['code']) && $setAt
                && Carbon::parse($setAt)->addDays(30)->isFuture()) {
                $referrer = User::where('referral_code', $payload['code'])->first();
            }
        }

        // Self-referral guard juga berlaku untuk atribusi cookie.
        if ($referrer && normalize_phone((string) $referrer->no_hp) === normalize_phone((string) ($data['no_hp'] ?? ''))) {
            $referrer = null;
        }

        $user = User::create($data + [
            'role' => User::ROLE_CUSTOMER,
            'referred_by' => $referrer?->id,
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
