<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Spammer;
use App\Models\SpammerIp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'no_hp' => $this->normalizePhone((string) $request->input('no_hp')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')],
            'no_hp' => ['required', 'string', 'regex:/^(\+?62|0)8[1-9][0-9]{6,11}$/', Rule::unique('users', 'no_hp')],
            'tgl_lahir' => ['required', 'date', 'before:today'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'alamat_lengkap' => ['required', 'string', 'min:10', 'max:500'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'tnc' => ['accepted'],
            'privacy' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:16'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan login.',
            'no_hp.required' => 'No HP wajib diisi.',
            'no_hp.regex' => 'Format No HP tidak valid. Contoh: 08123456789 atau 628123456789.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'provinsi.required' => 'Provinsi wajib diisi.',
            'kota.required' => 'Kota/Kabupaten wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.min' => 'Alamat lengkap minimal 10 karakter.',
            'kode_pos.required' => 'Kode pos wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'tnc.accepted' => 'Kamu harus menyetujui Syarat & Ketentuan.',
            'privacy.accepted' => 'Kamu harus menyetujui Kebijakan Privasi.',
        ]);

        $noHp = $validated['no_hp'];
        $existingCustomer = Customer::whereHas('user', function (Builder $query) use ($validated, $noHp): void {
            $query->where('email', $validated['email'])
                ->orWhere('no_hp', $noHp);
        })->exists();

        if ($existingCustomer) {
            return back()->withInput()->withErrors([
                'email' => 'Email atau No HP ini sudah pernah belanja. Silakan login atau reset password.',
            ]);
        }

        $matchingSpammers = fn () => Spammer::query()
            ->where(function (Builder $query) use ($validated, $noHp): void {
                $query->where('email', $validated['email'])
                    ->orWhere('no_hp', $noHp);
            });
        $expiredWatchlist = $matchingSpammers()
            ->where('status', 'watchlist')
            ->whereNotNull('unblocked_until')
            ->where('unblocked_until', '<=', now())
            ->get();
        foreach ($expiredWatchlist as $spammer) {
            $spammer->update(['status' => 'blocked', 'unblocked_until' => null]);
            $spammer->logs()->create([
                'action' => 'grace_expired',
                'ip_address' => $request->ip(),
                'note' => 'Masa grace period berakhir sebelum pendaftaran customer.',
            ]);
        }

        $identityBlocked = $matchingSpammers()->where('status', 'blocked')->exists();
        $ipBlocked = $request->ip() && SpammerIp::query()
            ->where('ip_address', $request->ip())
            ->whereHas('spammer', fn (Builder $query) => $query->where('status', 'blocked'))
            ->exists();

        if ($identityBlocked || $ipBlocked) {
            $this->recordSpammerAttempt($validated['email'], $noHp);

            return back()->withInput()->withErrors([
                'email' => 'Email atau No HP ini tidak dapat mendaftar.',
            ]);
        }

        $inGracePeriod = $matchingSpammers()
            ->where('status', 'watchlist')
            ->where('unblocked_until', '>', now())
            ->exists();
        $attemptCount = $this->getRegisterAttemptCount($validated['email'], $noHp);
        if (! $inGracePeriod && $attemptCount >= 3) {
            $this->addToSpammer($validated['email'], $noHp, 'no_purchase_3x');

            return back()->withInput()->withErrors([
                'email' => 'Kamu sudah 3x mendaftar tanpa melakukan pembelian. Akun tidak dapat didaftarkan lagi.',
            ]);
        }

        $user = DB::transaction(function () use ($validated, $noHp, $request): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'no_hp' => $noHp,
                'tgl_lahir' => $validated['tgl_lahir'],
                'provinsi' => $validated['provinsi'],
                'kota' => $validated['kota'],
                'kecamatan' => $validated['kecamatan'],
                'alamat_lengkap' => $validated['alamat_lengkap'],
                'kode_pos' => $validated['kode_pos'],
                'role' => 'customer',
                'referral_code' => $this->generateReferralCode(),
                'invited_by_code' => $validated['referral_code'] ?? null,
            ]);

            $user->crush()->create([
                'verified_at' => null, // di-set setelah verifikasi email
                'expires_at' => now()->addDays(30),
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'register_attempt',
                'description' => 'User registered',
                'url' => $request->fullUrl(),
                'ip_address' => function_exists('get_real_ip') ? get_real_ip() : $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['email' => $validated['email'], 'no_hp' => $noHp],
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Akun berhasil dibuat! Cek email untuk verifikasi.')
            ->with('ga4_events', [[
                'name' => 'sign_up',
                'params' => ['method' => 'email'],
            ]]);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    protected function getRegisterAttemptCount(string $email, string $phone): int
    {
        return ActivityLog::where('action', 'register_attempt')
            ->where(function ($q) use ($email, $phone) {
                $q->where('metadata->email', $email)
                    ->orWhere('metadata->no_hp', $phone);
            })
            ->count();
    }

    protected function recordSpammerAttempt(string $email, string $phone): void
    {
        $ip = request()->ip();
        ActivityLog::create([
            'user_id' => null,
            'action' => 'register_attempt',
            'description' => 'Attempt register by blocked email/phone',
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'metadata' => ['email' => $email, 'no_hp' => $phone],
        ]);

        $spammers = Spammer::query()
            ->where('status', 'blocked')
            ->where(function (Builder $query) use ($email, $phone, $ip): void {
                $query->where('email', $email)->orWhere('no_hp', $phone);
                if ($ip) {
                    $query->orWhereHas('ips', fn (Builder $ipQuery) => $ipQuery->where('ip_address', $ip));
                }
            })
            ->get();

        foreach ($spammers as $spammer) {
            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP)) {
                continue;
            }

            $ipRecord = SpammerIp::query()->firstOrNew([
                'spammer_id' => $spammer->id,
                'ip_address' => $ip,
            ]);
            $ipRecord->attempt_count = $ipRecord->exists ? $ipRecord->attempt_count + 1 : 1;
            $ipRecord->last_attempt_at = now();
            $ipRecord->save();
        }
    }

    protected function addToSpammer(string $email, string $phone, string $reason): void
    {
        $spammer = Spammer::query()
            ->where(fn (Builder $query) => $query->where('email', $email)->orWhere('no_hp', $phone))
            ->first();

        if ($spammer) {
            $spammer->update(['reason' => $reason, 'status' => 'blocked', 'unblocked_until' => null]);
        } else {
            $spammer = Spammer::create([
                'email' => $email,
                'no_hp' => $phone,
                'reason' => $reason,
                'status' => 'blocked',
            ]);
        }

        $spammer->logs()->create([
            'action' => 'automatically_blocked',
            'ip_address' => request()->ip(),
            'note' => 'Pendaftaran ketiga tanpa transaksi.',
        ]);
        $ip = request()->ip();
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            $spammer->ips()->updateOrCreate(
                ['ip_address' => $ip],
                ['attempt_count' => 1, 'last_attempt_at' => now()],
            );
        }
    }
}
