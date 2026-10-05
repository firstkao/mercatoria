<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Kontak
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_hours' => ['nullable', 'string', 'max:150'],
            'store_address' => ['nullable', 'string', 'max:500'],

            // Sosial media dinamis (tabel social_media; disimpan via halaman Umum ini).
            'socials' => ['nullable', 'array'],
            'socials.*.id' => ['nullable', 'integer'],
            'socials.*.name' => ['required_with:socials', 'string', 'max:100'],
            'socials.*.url' => ['nullable', 'url', 'max:255'],
            'socials.*.icon_url' => ['nullable', 'url', 'max:500'],
            'socials.*.icon_key' => ['nullable', 'string', 'max:50'],
            'socials.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'socials.*.is_active' => ['nullable', 'boolean'],
            'socials.*.delete' => ['nullable', 'boolean'],

            // WA widget (Batch 23)
            'wa_widget_enabled' => ['nullable', 'boolean'],
            'wa_widget_greeting' => ['nullable', 'string', 'max:500'],

            // Cart reminder (Batch 31)
            'cart_reminder_1_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'cart_reminder_2_hours' => ['nullable', 'integer', 'min:2', 'max:168'],

            // Best seller (Batch 30)
            'best_seller_period_days' => ['nullable', 'integer', 'in:30,90,180,365,730'],
            'best_seller_limit' => ['nullable', 'integer', 'min:4', 'max:20'],
            'best_seller_min_sales' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Email verification (Batch 32)
            'email_verification_enabled' => ['nullable', 'boolean'],

            // Maintenance (Batch 28)
            'maintenance_enabled' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'maintenance_bypass_ips' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'contact_email' => 'email kontak',
            'contact_whatsapp' => 'WhatsApp CS',
            'contact_hours' => 'jam operasional',
            'store_address' => 'alamat toko',
            'social_instagram' => 'Instagram',
            'social_tiktok' => 'TikTok',
            'social_facebook' => 'Facebook',
            'social_x' => 'X',
            'wa_widget_enabled' => 'tombol WhatsApp',
            'wa_widget_greeting' => 'pesan sapaan WA',
            'cart_reminder_1_hours' => 'reminder cart #1',
            'cart_reminder_2_hours' => 'reminder cart #2',
            'best_seller_period_days' => 'periode best seller',
            'best_seller_limit' => 'jumlah best seller',
            'best_seller_min_sales' => 'minimal penjualan',
            'email_verification_enabled' => 'verifikasi email',
            'maintenance_enabled' => 'mode pemeliharaan',
            'maintenance_message' => 'pesan pemeliharaan',
            'maintenance_bypass_ips' => 'IP bypass',
        ];
    }
}
