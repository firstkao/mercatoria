<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
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
        // 1) Klik link -> pengajak dapat +10 koin (anti-spam: 1x per IP SELAMANYA per kode)
        // 2) Daftar & beli -> pengajak dapat +5000 koin (lihat rewardReferral di OrderManagementController)
        $refCode = strtoupper(trim((string) $request->query('ref')));

        if ($refCode !== '') {
            // ✅ BUG FIX: dulu pakai `$request->ip()`. Kalau situs berada di balik
            // Cloudflare/proxy, nilai itu = IP CDN → SEMUA pengunjung dianggap
            // satu IP yang sama, jadi hanya klik pertama yang pernah memberi koin
            // (pengajak rugi). Sebaliknya kalau proxy di-trust semua, header
            // X-Forwarded-For bisa dipalsukan untuk mencetak koin tanpa batas.
            // Sekarang pakai helper yang sama dengan Gatekeeper (IP blacklist).
            $this->rewardReferralClick($refCode, SecurityHelper::getRealIp($request));
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
     * Idempotent PERMANEN per (referrer, ip) berkat unique index referral_clicks:
     * satu IP hanya pernah dihitung sekali seumur hidup untuk kode pengajak itu,
     * bukan sekali per hari.
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

            // Sudah pernah dihitung dari IP ini (kapan pun, selamanya)? Skip (tanpa insert).
            $already = ReferralClick::query()
                ->where('referrer_id', $referrer->id)
                ->where('ip_address', $ip)
                ->exists();

            if ($already) {
                return;
            }

            $amount = Setting::integer('referral_reward_click', 10);

            // ✅ BUG FIX: insert klik + pembuatan koin jadi SATU transaksi.
            // Dulu klik dicatat lebih dulu dan koin dibuat sesudahnya: kalau
            // CoinLot::create() gagal (kursus/DB error, disk penuh, dsb.), baris
            // klik tetap tersimpan → pengajak kehilangan koinnya SELAMANYA
            // karena unique index menganggap IP itu sudah pernah dihitung.
            // Dengan transaksi, kegagalan koin ikut membatalkan klik (bisa dicoba
            // ulang), dan unique index tetap mencegah dobel saat request paralel.
            $lot = DB::transaction(function () use ($referrer, $ip, $amount): ?CoinLot {
                // Insert dulu sebagai "kunci" anti-race (unique index menolak duplikat).
                ReferralClick::create([
                    'referrer_id' => $referrer->id,
                    'ip_address' => $ip,
                    'clicked_on' => now()->toDateString(),
                ]);

                if ($amount <= 0) {
                    return null;
                }

                return CoinLot::create([
                    'user_id' => $referrer->id,
                    'source' => 'referral_click',
                    'order_id' => null,
                    'amount' => $amount,
                    'remaining' => $amount,
                    'earned_at' => now(),
                    'expires_at' => now()->addMonths(Setting::integer('coin_expiry_months', 12)),
                ]);
            });

            if ($lot !== null) {
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