<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Whitelist key yang boleh diubah lewat halaman Settings.
     *
     * @var list<string>
     */
    private const VALID_KEYS = [
        'admin_notify_payment_upload',
        'anti_download_enabled',
        'auto_cancel_dp_days',
        'auto_delete_crush_days',
        'auto_delete_payment_proof',
        'auto_watermark_enabled',
        'bell_auto_delete_read',
        'bell_dropdown_limit',
        'bell_enabled',
        'bell_per_page',
        'bell_polling_seconds',
        'bell_retention_days',
        'bell_toast_enabled',
        'best_offers_count',
        'bot_whitelist_ai',
        'bot_whitelist_search',
        'bot_whitelist_social',
        'cart_reminder_minutes',
        'catalog_per_page',
        'coin_birthday_bonus',
        'coin_expiry_days',
        'coin_max_discount_percent',
        'coin_order_percent',
        'coin_referral_referee',
        'coin_referral_referrer',
        'coin_share',
        'customer_note_max_chars',
        'default_weight_gram',
        'display_timezone',
        'dp_minimum_percent',
        'email_cs',
        'email_notification',
        'email_security',
        'email_stop_reason',
        'email_verification_hours',
        'fee_qris_percent',
        'fee_shopee_dp',
        'fee_shopee_fp',
        'fee_toco_dp',
        'fee_toco_fp',
        'forever_delete_enabled',
        'ga4_measurement_id',
        'heavy_watermark',
        'image_protection_enabled',
        'image_watermark_enabled',
        'image_watermark_opacity',
        'inject_spammer_max_attempt',
        'invoice_prefix',
        'late_penalty_max_days',
        'late_penalty_pay_hours',
        'late_penalty_per_day',
        'late_penalty_pickup_days',
        'log_retention_days',
        'login_lockout_minutes',
        'login_rate_limit_per_minute',
        'max_auto_batches',
        'max_register_without_purchase',
        'note_to_customer_max_chars',
        'note_to_customer_send_email',
        'notif_admin_bukti_bayar',
        'ongkir_btm_jkt_per_kg',
        'ongkir_cn_btm_per_kg',
        'pause_customer_emails',
        'payment_deadline_days',
        'price_rounding',
        'product_bulk_delete_enabled',
        'product_delete_confirm_enabled',
        'profit_percent',
        'promo_bar_active',
        'promo_bar_discount',
        'promo_bar_link',
        'promo_bar_min_purchase',
        'promo_bar_text',
        'qris_threshold',
        'rate_rmb_idr',
        'recently_viewed_count',
        'related_products_count',
        'share_restrict_id_only',
        'snapshot_image_path_prefix',
        'snapshot_image_retention',
        'state_notify_h7_enabled',
        'state_threshold_ex_days',
        'state_threshold_ons_days',
        'tier_gold_threshold',
        'tier_platinum_threshold',
        'tier_silver_threshold',
        'tier_trigger_status',
        'unblock_grace_days',
        'url_facebook',
        'url_instagram',
        'url_shopee',
        'url_threads',
        'url_toco',
        'url_whatsapp',
        'url_x',
        'view_limit',
        'voucher_birthday_min_belanja',
        'voucher_birthday_nominal',
        'watermark_opacity',
        'watermark_pattern',
        'watermark_text',
    ];

    public function index(): View
    {
        $this->ensureDefaultSettings();

        return view('admin.settings.index', [
            'groups' => Setting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
            'rateLastChange' => $this->rateLastChange(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureDefaultSettings();

        $input = (array) $request->input('settings', []);

        // [FIX] Checkbox HTML: unchecked = tidak terkirim = harus diset '0'.
        $checkboxKeys = [
            'pause_customer_emails', 'notif_admin_bukti_bayar',
            'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
        ];
        foreach ($checkboxKeys as $ck) {
            if (! array_key_exists($ck, $input)) {
                $input[$ck] = '0';
            } elseif ($input[$ck] !== '1') {
                $input[$ck] = '0';
            }
        }

        $unknownKeys = array_diff(array_keys($input), self::VALID_KEYS);
        if ($unknownKeys !== []) {
            throw ValidationException::withMessages(
                collect($unknownKeys)->mapWithKeys(fn ($key) => ["settings.{$key}" => 'Pengaturan tidak dikenal.'])->all()
            );
        }

        // Validasi
        $rules = $this->rules($input);
        $validator = validator($input, $rules);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                foreach ($messages as $message) {
                    $errors["settings.{$key}"][] = $message;
                }
            }
            throw ValidationException::withMessages($errors);
        }

        // Simpan semua settings (auto-create jika belum ada)
        foreach ($input as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $setting = Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $value,
                    'type' => $this->guessType($key),
                    'group' => $this->guessGroup($key),
                    'description' => $this->guessDescription($key),
                ]
            );

            $oldValue = $setting->value;
            $newValue = (string) $value;

            if ($oldValue !== $newValue) {
                $setting->update(['value' => $newValue]);

                AdminLog::record(
                    $request->user(),
                    'update',
                    'Setting',
                    $setting->id,
                    ['value' => $oldValue],
                    ['value' => $newValue],
                    "Ubah setting {$key}"
                );
            }
        }

        Cache::forget('app_settings_all');

        return redirect()->route('office.settings.index')->with('status', 'Settings disimpan.');
    }

    private function rateLastChange(): ?AdminLog
    {
        $setting = Setting::where('key', 'rate_rmb_idr')->first();

        if (! $setting) {
            return null;
        }

        return AdminLog::with('admin')
            ->where('model', 'Setting')
            ->where('model_id', $setting->id)
            ->where('action', 'update')
            ->latest('id')
            ->first();
    }

    /**
     * PRD 22.2/29: halaman pengaturan SEO (GA4 Measurement ID, dsb).
     */
    public function seo(): View
    {
        return view('admin.seo.index', [
            'ga4' => (string) setting('ga4_measurement_id', ''),
        ]);
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ga4_measurement_id' => ['nullable', 'string', 'max:40'],
        ]);

        $this->saveSetting($request, 'ga4_measurement_id', (string) ($data['ga4_measurement_id'] ?? ''), 'seo');

        return redirect()->route('office.seo.index')->with('status', 'Pengaturan SEO disimpan.');
    }

    /**
     * PRD 22.2/40.1: halaman pengaturan marketing (promo bar).
     */
    public function marketing(): View
    {
        return view('admin.marketing.index', [
            'promo_bar_text' => (string) setting('promo_bar_text', ''),
            'promo_bar_link' => (string) setting('promo_bar_link', ''),
            'promo_bar_active' => (string) setting('promo_bar_active', '1'),
        ]);
    }

    public function updateMarketing(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promo_bar_text' => ['nullable', 'string', 'max:500'],
            'promo_bar_link' => ['nullable', 'string', 'max:255', 'regex:~^(https?://|/)~i'],
            'promo_bar_active' => ['nullable', 'boolean'],
        ]);

        $this->saveSetting($request, 'promo_bar_text', (string) ($data['promo_bar_text'] ?? ''), 'marketing');
        $this->saveSetting($request, 'promo_bar_link', (string) ($data['promo_bar_link'] ?? ''), 'marketing');
        $this->saveSetting($request, 'promo_bar_active', $request->boolean('promo_bar_active') ? '1' : '0', 'marketing');

        return redirect()->route('office.marketing.index')->with('status', 'Pengaturan marketing disimpan.');
    }

    /**
     * [FIX] PRD 15.2: kirim email test ke admin — bypass kill-switch.
     * Dipanggil via fetch() dari blade settings (modal Kirim Email Test).
     */
    public function testEmail(Request $request): JsonResponse
    {
        $adminEmail = $request->user()->email;

        try {
            Mail::raw(
                "Ini adalah email test dari Mercatoria Admin Panel.\n\n".
                'Waktu: '.now()->format('Y-m-d H:i:s')."\n\n".
                'Jika Anda menerima email ini, konfigurasi SMTP sudah benar.',
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('[Test] Mercatoria — Email Test');
                }
            );

            AdminLog::record(
                $request->user(),
                'create',
                'Setting',
                null,
                null,
                ['email' => $adminEmail],
                'Kirim email test'
            );

            return response()->json([
                'success' => true,
                'message' => "Email test terkirim ke {$adminEmail}. Silakan cek inbox / folder spam.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email test: '.$e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════

    /**
     * Pastikan semua settings default ada di DB (idempotent).
     */
    private function ensureDefaultSettings(): void
    {
        $defaults = [
            // ── Harga & Pengiriman (DS 15.1) ──
            ['key' => 'rate_rmb_idr',               'group' => 'pricing',  'type' => 'string', 'value' => '3000',      'description' => 'Rate RMB ke IDR'],
            ['key' => 'profit_percent',              'group' => 'pricing',  'type' => 'string', 'value' => '11',        'description' => 'Profit persen'],
            ['key' => 'ongkir_cn_btm_per_kg',        'group' => 'pricing',  'type' => 'string', 'value' => '25000',     'description' => 'Ongkir CN ke Batam per kg'],
            ['key' => 'ongkir_btm_jkt_per_kg',       'group' => 'pricing',  'type' => 'string', 'value' => '60000',     'description' => 'Ongkir Batam ke Jakarta per kg'],
            ['key' => 'default_weight_gram',         'group' => 'pricing',  'type' => 'string', 'value' => '0',         'description' => 'Default berat produk gram'],
            ['key' => 'fee_toco_fp',                 'group' => 'pricing',  'type' => 'string', 'value' => '1',         'description' => 'Fee Toco FP'],
            ['key' => 'fee_toco_dp',                 'group' => 'pricing',  'type' => 'string', 'value' => '0',         'description' => 'Fee Toco DP'],
            ['key' => 'fee_shopee_fp',               'group' => 'pricing',  'type' => 'string', 'value' => '109',       'description' => 'Fee Shopee FP'],
            ['key' => 'fee_shopee_dp',               'group' => 'pricing',  'type' => 'string', 'value' => '25',        'description' => 'Fee Shopee DP persen'],
            ['key' => 'fee_qris_percent',            'group' => 'pricing',  'type' => 'string', 'value' => '0.03',      'description' => 'Fee QRIS persen'],
            ['key' => 'qris_threshold',              'group' => 'pricing',  'type' => 'string', 'value' => '500000',    'description' => 'Threshold QRIS'],
            ['key' => 'dp_minimum_percent',          'group' => 'pricing',  'type' => 'string', 'value' => '50',        'description' => 'DP minimum persen'],

            // ── Email (DS 15.2) ──
            ['key' => 'pause_customer_emails',       'group' => 'email',    'type' => 'bool',   'value' => '0',         'description' => 'Stop kirim email ke customer'],
            ['key' => 'email_stop_reason',           'group' => 'email',    'type' => 'string', 'value' => '',          'description' => 'Alasan stop email'],
            ['key' => 'email_security',              'group' => 'email',    'type' => 'string', 'value' => 'security@mercatoria.id',     'description' => 'Email keamanan'],
            ['key' => 'email_notification',          'group' => 'email',    'type' => 'string', 'value' => 'info@mercatoria.id',         'description' => 'Email notifikasi sistem'],
            ['key' => 'email_cs',                    'group' => 'email',    'type' => 'string', 'value' => 'cs@mercatoria.id',           'description' => 'Email customer service'],
            ['key' => 'notif_admin_bukti_bayar',     'group' => 'email',    'type' => 'bool',   'value' => '1',         'description' => 'Notif admin bukti bayar'],

            // ── Security / Watermark (DS 15.3) ──
            ['key' => 'anti_download_enabled',       'group' => 'security', 'type' => 'bool',   'value' => '0',         'description' => 'Proteksi anti-drag anti-download foto'],
            ['key' => 'auto_watermark_enabled',      'group' => 'security', 'type' => 'bool',   'value' => '0',         'description' => 'Auto watermark server-side'],
            ['key' => 'watermark_text',              'group' => 'security', 'type' => 'string', 'value' => 'MERCATORIA · mercatoria.id', 'description' => 'Teks watermark'],
            ['key' => 'watermark_pattern',           'group' => 'security', 'type' => 'string', 'value' => 'diagonal_tiled',             'description' => 'Pola watermark'],
            ['key' => 'watermark_opacity',           'group' => 'security', 'type' => 'string', 'value' => '25',        'description' => 'Opacity watermark persen'],
            ['key' => 'heavy_watermark',             'group' => 'security', 'type' => 'bool',   'value' => '0',         'description' => 'Heavy watermark jika URL dibuka langsung'],

            // ── SEO ──
            ['key' => 'ga4_measurement_id',          'group' => 'seo',      'type' => 'string', 'value' => '',          'description' => 'Google Analytics 4 Measurement ID'],

            // ── Marketing ──
            ['key' => 'promo_bar_text',              'group' => 'marketing', 'type' => 'string', 'value' => '',          'description' => 'Teks promo bar'],
            ['key' => 'promo_bar_link',              'group' => 'marketing', 'type' => 'string', 'value' => '',          'description' => 'Link promo bar'],
            ['key' => 'promo_bar_active',            'group' => 'marketing', 'type' => 'bool',   'value' => '1',         'description' => 'Status promo bar'],
        ];

        foreach ($defaults as $d) {
            Setting::firstOrCreate(
                ['key' => $d['key']],
                [
                    'value' => $d['value'],
                    'type' => $d['type'],
                    'group' => $d['group'],
                    'description' => $d['description'],
                ]
            );
        }
    }

    /**
     * Validasi per key.
     *
     * [FIX] match(true) evaluasi berurutan.
     * Field persentase dicek SEBELUM field numeric umum.
     */
    private function rules(array $input): array
    {
        $rules = [];

        // [FIX] fee_shopee_dp dipindah ke sini — blade tampil '%', harus max:100.
        $percentKeys = [
            'profit_percent', 'fee_qris_percent', 'dp_minimum_percent',
            'watermark_opacity', 'fee_shopee_dp',
        ];

        // Numeric umum (tanpa overlap dengan percentKeys).
        $numericKeys = [
            'ongkir_cn_btm_per_kg', 'ongkir_btm_jkt_per_kg',
            'default_weight_gram', 'fee_toco_fp', 'fee_toco_dp',
            'fee_shopee_fp',
            'qris_threshold',
        ];

        $boolKeys = [
            'pause_customer_emails', 'notif_admin_bukti_bayar',
            'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
        ];

        $emailKeys = ['email_security', 'email_notification', 'email_cs'];

        foreach ($input as $key => $value) {
            $rules[$key] = match (true) {
                // Bool fields
                in_array($key, $boolKeys) => ['required', 'in:0,1'],

                $key === 'rate_rmb_idr' => ['required', 'numeric', 'min:1000', 'max:10000'],

                // Percentage fields (0-100) — dicek SEBELUM numeric umum
                in_array($key, $percentKeys) => ['required', 'numeric', 'min:0', 'max:100'],

                // Pembagi / batas / jumlah baris: 0 menyebabkan DivisionByZero atau paginasi rusak.
                $key === 'price_rounding' => ['required', 'integer', 'min:1'],
                $key === 'catalog_per_page' => ['required', 'integer', 'min:1', 'max:100'],
                in_array($key, ['late_penalty_pay_hours', 'late_penalty_pickup_days', 'log_retention_days', 'auto_delete_crush_days', 'login_rate_limit_per_minute']) => ['required', 'integer', 'min:1'],
                in_array($key, ['best_offers_count', 'related_products_count']) => ['required', 'integer', 'min:0', 'max:50'],
                in_array($key, ['late_penalty_per_day', 'late_penalty_max_days', 'coin_birthday_bonus', 'voucher_birthday_nominal', 'voucher_birthday_min_belanja', 'tier_gold_threshold', 'tier_platinum_threshold', 'promo_bar_discount', 'promo_bar_min_purchase']) => ['required', 'integer', 'min:0'],

                // Numeric / price fields (tanpa overlap)
                in_array($key, $numericKeys) => ['required', 'numeric', 'min:0'],

                // Email fields
                in_array($key, $emailKeys) => ['nullable', 'email', 'max:255'],

                // Watermark pattern
                $key === 'watermark_pattern' => ['nullable', 'string', 'in:diagonal_tiled,center,bottom_right,corner_4'],

                // Email stop reason (textarea)
                $key === 'email_stop_reason' => ['nullable', 'string', 'max:1000'],

                // Watermark text
                $key === 'watermark_text' => ['nullable', 'string', 'max:255'],

                // Default (semua lainnya)
                default => ['nullable', 'string', 'max:500'],
            };
        }

        return $rules;
    }

    /**
     * Tebak tipe setting dari key-nya.
     */
    private function guessType(string $key): string
    {
        if (in_array($key, [
            'pause_customer_emails', 'notif_admin_bukti_bayar',
            'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
            'promo_bar_active',
        ])) {
            return 'bool';
        }

        return 'string';
    }

    /**
     * Tebak group setting dari key-nya.
     */
    private function guessGroup(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'rate_'), str_starts_with($key, 'profit_'),
            str_starts_with($key, 'ongkir_'), str_starts_with($key, 'default_weight'),
            str_starts_with($key, 'fee_'), str_starts_with($key, 'qris_'),
            str_starts_with($key, 'dp_') => 'pricing',

            str_starts_with($key, 'email_'), str_starts_with($key, 'notif_'),
            $key === 'pause_customer_emails' => 'email',

            str_starts_with($key, 'watermark_'), str_starts_with($key, 'anti_'),
            str_starts_with($key, 'auto_watermark'), $key === 'heavy_watermark' => 'security',

            str_starts_with($key, 'ga4_') => 'seo',

            str_starts_with($key, 'promo_') => 'marketing',

            default => 'display',
        };
    }

    /**
     * Tebak deskripsi dari key-nya.
     */
    private function guessDescription(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Simpan single setting — dengan parameter group.
     */
    private function saveSetting(Request $request, string $key, string $value, string $group = 'marketing'): void
    {
        $setting = Setting::firstOrCreate(['key' => $key], [
            'value' => $value,
            'type' => 'string',
            'group' => $group,
            'description' => $this->guessDescription($key),
        ]);

        $old = $setting->value;

        if ($setting->wasRecentlyCreated) {
            AdminLog::record($request->user(), 'create', 'Setting', $setting->id, null, ['value' => $value], "Buat setting {$key}");
        } else {
            $setting->update(['value' => $value]);

            if ((string) $old !== $value) {
                AdminLog::record($request->user(), 'update', 'Setting', $setting->id,
                    ['value' => $old], ['value' => $value],
                    "Ubah setting {$key}");
            }
        }

        Cache::forget('app_settings_all');
    }
}
