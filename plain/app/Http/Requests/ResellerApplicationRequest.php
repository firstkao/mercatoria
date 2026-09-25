<?php

namespace App\Http\Requests;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ResellerApplication;
use App\Rules\Turnstile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResellerApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => Str::squish((string) $this->input('full_name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'whatsapp' => RegisterRequest::normalizeWhatsapp((string) $this->input('whatsapp')),
        ]);
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
            'whatsapp' => ['required', 'regex:/^628\d{7,11}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'sales_channel' => ['required', Rule::in(array_keys(ResellerApplication::SALES_CHANNELS))],
            'store_link' => ['nullable', 'url:https,http', 'max:255'],
            'monthly_estimate' => ['required', Rule::in(array_keys(ResellerApplication::MONTHLY_ESTIMATES))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'accept_reseller_terms' => ['accepted'],
            'cf-turnstile-response' => [new Turnstile($this->ip())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.regex' => 'Nama hanya boleh huruf dan spasi, minimal 2 kata, sesuai identitas.',
            'whatsapp.regex' => 'Nomor WhatsApp harus nomor Indonesia (awali dengan 08 atau +62).',
            'store_link.url' => 'Link toko harus berupa alamat web lengkap, contoh: https://instagram.com/namatoko',
            'accept_reseller_terms.accepted' => 'Kamu wajib menyetujui ketentuan reseller.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'whatsapp' => 'nomor WhatsApp',
            'city' => 'kota/kabupaten',
            'sales_channel' => 'tempat berjualan',
            'store_link' => 'link toko',
            'monthly_estimate' => 'perkiraan order per bulan',
            'notes' => 'catatan',
        ];
    }
}