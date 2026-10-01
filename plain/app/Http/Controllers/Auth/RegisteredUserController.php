<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ActivityLog;
use App\Models\CoinLot;
use App\Models\IdentityRecord;
use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\LegalContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        // Satu link untuk dua fungsi: /daftar?ref=KODE
        // 1) Klik link -> pengajak dapat +10 koin (anti-spam: 1x per IP per hari per kode)
        // 2) Daftar & beli -> pengajak dapat +5000 koin (lihat rewardReferral di OrderManagementController)
        $refCode = strtoupper(trim((string) $request->query('ref')));

        if ($refCode !== '') {
            $this->rewardReferralClick($refCode, $request->ip());
        }

        return view('auth.register', [
            'provinces' => config('wilayah.provinces'),
            'termsHtml' => LegalContent::html('syarat-dan-ketentuan'),
            'privacyHtml' => LegalContent::html('kebijakan-privasi'),
            'prefilledReferral' => $refCode,
        ]);
    }

    /**
     * Reward klik link referral (+10 koin default).
     * Idempotent per (referrer, ip, tanggal) berkat unique index referral_clicks.
     */
    private function rewardReferralClick(string $refCode, ?string $ip): void
    {
        if (! $ip) {
            return;
        }

        try {
            $referrer = User::query()->where('referral_code', $refCode)->first();

            if (! $referrer) {
                return; // kode tidak dikenal -> anggap saja halaman daftar biasa
            }

            $today = now()->toDateString();

            // Sudah pernah dihitung hari ini dari IP ini? Skip (tanpa insert).
            $already = ReferralClick::query()
                ->where('referrer_id', $referrer->id)
                ->where('ip_address', $ip)
                ->where('clicked_on', $today)
                ->exists();

            if ($already) {
                return;
            }

            // Insert dulu sebagai "kunci" anti-race (unique index menolak duplikat).
            ReferralClick::create([
                'referrer_id' => $referrer->id,
                'ip_address' => $ip,
                'clicked_on' => $today,
            ]);

            $amount = Setting::integer('referral_reward_click', 10);

            if ($amount > 0) {
                CoinLot::create([
                    'user_id' => $referrer->id,
                    'source' => 'referral_click',
                    'order_id' => null,
                    'amount' => $amount,
                    'remaining' => $amount,
                    'earned_at' => now(),
                    'expires_at' => now()->addMonths(Setting::integer('coin_expiry_months', 12)),
                ]);

                try {
                    NotificationService::coinsEarned($referrer, $amount, 'referral_click');
                } catch (\Throwable $e) {
                    Log::warning('Gagal kirim notifikasi koin klik referral', [
                        'referrer_id' => $referrer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Race condition: IP yang sama sudah tercatat sedetik lalu -> abaikan.
        } catch (\Throwable $e) {
            // Jangan sampai halaman register error karena tracking klik.
            Log::warning('Gagal memproses klik referral', ['error' => $e->getMessage()]);
        }
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