<?php

namespace App\Http\Requests\Auth;

use App\Models\IdentityRecord;
use App\Models\NameBlacklistEntry;
use App\Rules\Turnstile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    /**
     * Normalize names, phone numbers, and emails before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => Str::squish((string) $this->input('full_name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'whatsapp' => self::normalizeWhatsapp((string) $this->input('whatsapp')),
        ]);
    }

    /**
     * Convert +62 / 62 / 08 numbers to the 62xxx format.
     */
    public static function normalizeWhatsapp(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100', 'regex:/^\p{Latin}+(?: \p{Latin}+)+$/u'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'after:1900-01-01', 'before:today'],
            'province' => ['required', Rule::in(config('wilayah.provinces'))],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'digits:5'],
            'street_address' => ['required', 'string', 'max:500'],
            'whatsapp' => ['required', 'regex:/^628\d{7,11}$/', Rule::unique('users', 'whatsapp')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'parental_consent' => ['nullable', 'boolean'],
            'accept_terms' => ['accepted'],
            'accept_privacy' => ['accepted'],
            'cf-turnstile-response' => [new Turnstile($this->ip())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.regex' => 'Nama hanya boleh huruf dan spasi, minimal 2 kata, sesuai identitas.',
            'whatsapp.regex' => 'Nomor WhatsApp harus nomor Indonesia (awali dengan 08 atau +62).',
            'whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'accept_terms.accepted' => 'Kamu wajib menyetujui Syarat & Ketentuan.',
            'accept_privacy.accepted' => 'Kamu wajib menyetujui Kebijakan Privasi.',
        ];
    }

    /**
     * Get the validation checks that depend on several fields or stored data.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->has('full_name') && NameBlacklistEntry::matches($this->input('full_name'))) {
                    $validator->errors()->add('full_name', 'Gunakan nama asli sesuai identitas, bukan nama karakter.');
                }

                if (! $validator->errors()->has('birth_date') && $this->isUnderEighteen() && ! $this->boolean('parental_consent')) {
                    $validator->errors()->add('parental_consent', 'Pengguna di bawah 18 tahun wajib mendapat persetujuan orang tua/wali.');
                }

                if (! $validator->errors()->hasAny(['email', 'whatsapp'])
                    && IdentityRecord::anyBlocked($this->input('email'), $this->input('whatsapp'))) {
                    $validator->errors()->add('email', 'Email atau nomor WhatsApp ini diblokir. Hubungi admin melalui chat untuk mengajukan banding.');
                }
            },
        ];
    }

    /**
     * Determine whether the applicant is younger than 18 today in Jakarta time.
     */
    public function isUnderEighteen(): bool
    {
        $birthDate = Carbon::createFromFormat('Y-m-d', $this->input('birth_date'), 'Asia/Jakarta');

        return $birthDate->diffInYears(now('Asia/Jakarta')) < 18;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'birth_date' => 'tanggal lahir',
            'province' => 'provinsi',
            'city' => 'kota/kabupaten',
            'district' => 'kecamatan',
            'postal_code' => 'kode pos',
            'street_address' => 'alamat lengkap',
            'whatsapp' => 'nomor WhatsApp',
            'password' => 'kata sandi',
        ];
    }
}