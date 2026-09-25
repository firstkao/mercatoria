<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ActivityLog;
use App\Models\IdentityRecord;
use App\Models\Setting;
use App\Models\User;
use App\Support\LegalContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'provinces' => config('wilayah.provinces'),
            'termsHtml' => LegalContent::html('syarat-dan-ketentuan'),
            'privacyHtml' => LegalContent::html('kebijakan-privasi'),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $now = now();

            $user = User::create([
                ...$request->safe()->only([
                    'full_name', 'birth_date', 'province', 'city', 'district',
                    'postal_code', 'street_address', 'whatsapp', 'email', 'password',
                ]),
                'parental_consent' => $request->isUnderEighteen() && $request->boolean('parental_consent'),
                'tnc_accepted_at' => $now,
                'privacy_accepted_at' => $now,
                'registered_at' => $now,
                'expires_at' => $now->copy()->addDays(Setting::integer('spammer_ttl_days', 30)),
            ]);

            foreach ([IdentityRecord::KIND_EMAIL => $user->email, IdentityRecord::KIND_WHATSAPP => $user->whatsapp] as $kind => $value) {
                IdentityRecord::firstOrCreate(['kind' => $kind, 'value' => $value])
                    ->registrationEvents()
                    ->create(['event' => 'registered']);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        ActivityLog::record($user, 'register', $request);

        return redirect()->route('account.show');
    }
}