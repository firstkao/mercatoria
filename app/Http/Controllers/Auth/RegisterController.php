<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Crush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Tampilkan form register
     */
    public function showForm()
    {
        // Kalau udah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle submit form register
     */
    public function store(Request $request)
    {
        // ─── 1. Validasi ─────────────────────────────
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'regex:/^(\+?62|0)8[1-9][0-9]{6,11}$/'],
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

        // ─── 2. Normalisasi No HP ke format 62xxx ───
        $noHp = $this->normalizePhone($validated['no_hp']);

        // ─── 3. Cek Buyer Protection ─────────────────
        $existingCustomer = \App\Models\Customer::whereHas('user', function ($q) use ($validated, $noHp) {
            $q->where('email', $validated['email'])
              ->orWhere('no_hp', $noHp);
        })->exists();

        if ($existingCustomer) {
            return back()->withInput()->withErrors([
                'email' => 'Email atau No HP ini sudah pernah belanja. Silakan login atau reset password.',
            ]);
        }

        // ─── 4. Cek Spammer List ─────────────────────
        $isSpammer = \App\Models\Spammer::where('email', $validated['email'])
            ->orWhere('no_hp', $noHp)
            ->where('status', 'blocked')
            ->exists();

        if ($isSpammer) {
            // Increment attempt count
            $this->recordSpammerAttempt($validated['email'], $noHp);
            return back()->withInput()->withErrors([
                'email' => 'Email atau No HP ini tidak dapat mendaftar.',
            ]);
        }

        // ─── 5. Cek attempt count (3x daftar tanpa belanja) ──
        $attemptCount = $this->getRegisterAttemptCount($validated['email'], $noHp);
        if ($attemptCount >= 3) {
            $this->addToSpammer($validated['email'], $noHp, 'no_purchase_3x');
            return back()->withInput()->withErrors([
                'email' => 'Kamu sudah 3x mendaftar tanpa melakukan pembelian. Akun tidak dapat didaftarkan lagi.',
            ]);
        }

        // ─── 6. Bikin User + Crush dalam 1 Transaction ─
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
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

            // Bikin entry Crush (durasi 30 hari)
            Crush::create([
                'user_id' => $user->id,
                'verified_at' => null, // di-set setelah verifikasi email
                'expires_at' => now()->addDays(30),
            ]);

            DB::commit();

            // ─── 7. Auto-login ───────────────────────
            Auth::login($user);

            // ─── 8. Kirim email verifikasi ───────────
            // (untuk sekarang, skip pengiriman email — akan diaktifkan di Batch selanjutnya)
            // event(new Registered($user));

            // ─── 9. Redirect ─────────────────────────
            return redirect()->route('dashboard')
                ->with('success', 'Akun berhasil dibuat! Cek email untuk verifikasi.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Register failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'email' => 'Terjadi kesalahan. Silakan coba lagi.',
            ]);
        }
    }

    // ─── Helpers ─────────────────────────────────────

    /**
     * Normalisasi No HP ke format 62xxx
     */
    protected function normalizePhone(string $phone): string
    {
        // Hapus karakter non-digit kecuali +
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Kalau diawali 0, ganti jadi 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Kalau diawali 8, tambah 62
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Generate referral code unik (8 karakter)
     */
    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Hitung attempt count register dengan email/HP sama
     */
    protected function getRegisterAttemptCount(string $email, string $phone): int
    {
        return \App\Models\ActivityLog::where('action', 'register_attempt')
            ->where(function ($q) use ($email, $phone) {
                $q->where('metadata->email', $email)
                  ->orWhere('metadata->no_hp', $phone);
            })
            ->count();
    }

    /**
     * Catat register attempt ke activity log
     */
    protected function recordSpammerAttempt(string $email, string $phone): void
    {
        \App\Models\ActivityLog::create([
            'user_id' => null,
            'action' => 'register_attempt',
            'description' => 'Attempt register by blocked email/phone',
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['email' => $email, 'no_hp' => $phone],
        ]);
    }

    /**
     * Add email/HP ke spammer list
     */
    protected function addToSpammer(string $email, string $phone, string $reason): void
    {
        \App\Models\Spammer::create([
            'email' => $email,
            'no_hp' => $phone,
            'reason' => $reason,
            'status' => 'blocked',
        ]);
    }
}