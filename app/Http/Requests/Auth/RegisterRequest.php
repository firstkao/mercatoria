<?php

namespace App\Http\Requests\Auth;

use App\Models\Customer;
use App\Models\Spammer;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'tgl_lahir' => ['required', 'date'],
            'no_hp' => ['required', 'string', 'regex:/^(?:\+?62|0)8[1-9][0-9]{6,11}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:500'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree_tnc' => ['accepted'],
            'agree_privacy' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format nomor HP harus 08xxx atau 628xxx.',
            'agree_tnc' => 'Anda harus menyetujui Syarat & Ketentuan.',
            'agree_privacy' => 'Anda harus menyetujui Kebijakan Privasi.',
            'referral_code.exists' => 'Kode undangan tidak ditemukan.',
        ];
    }

    /**
     * Buyer Protection (PRD 11) + spammer (PRD 10) + batas 3x daftar (PRD 7.3).
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $email = (string) $this->input('email');
            $noHp = normalize_phone((string) $this->input('no_hp'));

            $blockedEmail = Spammer::query()
                ->where('status', Spammer::STATUS_BLOCKED)
                ->where('email', $email)
                ->exists();

            $blockedPhone = Spammer::query()
                ->where('status', Spammer::STATUS_BLOCKED)
                ->where('no_hp', $noHp)
                ->exists();

            if ($blockedEmail) {
                $validator->errors()->add('email', 'Email ini tidak dapat digunakan untuk mendaftar.');
            }

            if ($blockedPhone) {
                $validator->errors()->add('no_hp', 'Nomor HP ini tidak dapat digunakan untuk mendaftar.');
            }

            $isExistingCustomer = Customer::query()
                ->where('email', $email)
                ->orWhere('no_hp', $noHp)
                ->exists();

            if ($isExistingCustomer) {
                $validator->errors()->add('email', 'Email atau nomor WhatsApp ini sudah terdaftar sebagai pelanggan.');
                $this->session()->flash('buyer_protection', true);
            }

            $registrationCount = User::query()
                ->where('email', $email)
                ->orWhere('no_hp', $noHp)
                ->count();

            if ($registrationCount >= 3) {
                $validator->errors()->add('no_hp', 'Email dan nomor WhatsApp yang sama maksimal 3 kali mendaftar.');
            }

            // Self-referral: kode undangan milik orang dengan no HP yang sama ditolak.
            if ($this->filled('referral_code')) {
                $referrer = User::where('referral_code', $this->input('referral_code'))->first();

                if ($referrer && normalize_phone((string) $referrer->no_hp) === $noHp) {
                    $validator->errors()->add('referral_code', 'Kode undangan tidak dapat digunakan untuk diri sendiri.');
                }
            }

            // PRD 7.3 + 10.8: gagal daftar berulang dengan identitas sama -> auto-ban.
            $this->recordRegistrationFailure($email, $noHp);
        });
    }

    /**
     * Hitung gagal daftar per identitas (email/no_hp yang sudah dikenal sistem).
     * Mencapai 3x -> Spammer::create (reason=auto_ban, status=blocked) permanen (PRD 10.8).
     */
    private function recordRegistrationFailure(string $email, string $noHp): void
    {
        $known = User::where('email', $email)->orWhere('no_hp', $noHp)->exists()
            || Customer::where('email', $email)->orWhere('no_hp', $noHp)->exists()
            || Spammer::where('email', $email)->orWhere('no_hp', $noHp)->exists();

        if (! $known) {
            return;
        }

        foreach (array_filter([$email, $noHp]) as $anchor) {
            $key = 'register_fail:'.sha1($anchor);
            Cache::add($key, 0, now()->addDays(7));

            if (Cache::increment($key) < 3) {
                continue;
            }

            $spammer = Spammer::firstOrCreate(
                ['email' => $email],
                [
                    'no_hp' => $noHp,
                    'reason' => Spammer::REASON_AUTO_BAN,
                    'status' => Spammer::STATUS_BLOCKED,
                    'unblock_count' => 0,
                    'banned_at' => now(),
                ]
            );

            if ($spammer->wasRecentlyCreated) {
                app(AdminNotificationService::class)->notify(
                    'spammer_baru',
                    'Spammer baru (auto-ban): '.$email,
                    '/office/spammers'
                );
            }

            return;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function userData(): array
    {
        return [
            'name' => $this->input('name'),
            'tgl_lahir' => $this->input('tgl_lahir'),
            'no_hp' => normalize_phone((string) $this->input('no_hp')),
            'email' => $this->input('email'),
            'provinsi' => $this->input('provinsi'),
            'kota' => $this->input('kota'),
            'kecamatan' => $this->input('kecamatan'),
            'alamat' => $this->input('alamat'),
            'kode_pos' => $this->input('kode_pos'),
            'password' => $this->input('password'),
        ];
    }
}
