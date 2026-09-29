<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ActivityLog;
use App\Models\IdentityRecord;
use App\Models\Referral;
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
            'prefilledReferral' => strtoupper(trim((string) request()->query('ref'))),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $verificationEnabled = Setting::get('email_verification_enabled', '1') === '1';

        $user = DB::transaction(function () use ($request, $verificationEnabled): User {
            $now = now();

            $user = User::create([
                ...$request->safe()->only([
                    'full_name', 'birth_date', 'province', 'city', 'district',
                    'postal_code', 'street_address', 'whatsapp', 'email', 'password',
                ]),
                'referral_code' => User::generateReferralCode(),
                'email_verified_at' => $verificationEnabled ? null : $now,
                'parental_consent' => $request->isUnderEighteen() && $request->boolean('parental_consent'),
                'tnc_accepted_at' => $now,
                'privacy_accepted_at' => $now,
                'registered_at' => $now,
                'expires_at' => $now->copy()->addDays(Setting::integer('spammer_ttl_days', 30)),
            ]);

            // Catat referral kalau pakai kode undangan
            $referralCode = strtoupper(trim((string) $request->input('referral_code')));
            if ($referralCode !== '') {
                $referrer = User::query()->where('referral_code', $referralCode)->first();
                if ($referrer && $referrer->id !== $user->id) {
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referee_id' => $user->id,
                        'referral_code' => $referrer->referral_code,
                        'status' => Referral::STATUS_PENDING,
                    ]);
                }
            }

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

        // Kalau verifikasi email aktif → kirim OTP + arahkan ke halaman verify
        if ($verificationEnabled) {
            $user->sendEmailVerificationOtp();

            return redirect()->route('verification.notice')
                ->with('status', 'Akun berhasil dibuat. Cek email ' . $user->email . ' untuk kode verifikasi.');
        }

        return redirect()->route('account.show');
    }
}