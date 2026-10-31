<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        // Pastikan semua settings default ada di DB
        $this->ensureDefaultSettings();

        return view('admin.settings.index', [
            'groups' => Setting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureDefaultSettings();

        $input = (array) $request->input('settings', []);

        // Hapus key yang tidak dikirim (checkbox unchecked = 0)
        $checkboxKeys = [
            'customer_emails_paused', 'notif_admin_bukti_bayar',
            'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
        ];
        foreach ($checkboxKeys as $ck) {
            if (array_key_exists($ck, $input) && $input[$ck] !== '1') {
                $input[$ck] = '0';
            }
            // Checkbox yang tidak dikirim sama sekali = unchecked = 0
            if (!array_key_exists($ck, $input) && $request->has('settings')) {
                // Hanya set 0 jika form ini yang submit (bukan partial form)
            }
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
            if (!is_scalar($value)) continue;

            $setting = Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value'       => (string) $value,
                    'type'        => $this->guessType($key),
                    'group'       => $this->guessGroup($key),
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

        $this->saveSetting($request, 'ga4_measurement_id', (string) ($data['ga4_measurement_id'] ?? ''));

        return redirect()->route('office.seo.index')->with('status', 'Pengaturan SEO disimpan.');
    }

    /**
     * PRD 22.2/40.1: halaman pengaturan marketing (promo bar).
     */
    public function marketing(): View
    {
        return view('admin.marketing.index', [
            'promo_bar_text'   => (string) setting('promo_bar_text', ''),
            'promo_bar_link'   => (string) setting('promo_bar_link', ''),
            'promo_bar_active' => (string) setting('promo_bar_active', '1'),
        ]);
    }

    public function updateMarketing(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promo_bar_text'   => ['nullable', 'string', 'max:500'],
            'promo_bar_link'   => ['nullable', 'string', 'max:255'],
            'promo_bar_active' => ['nullable', 'boolean'],
        ]);

        $this->saveSetting($request, 'promo_bar_text', (string) ($data['promo_bar_text'] ?? ''));
        $this->saveSetting($request, 'promo_bar_link', (string) ($data['promo_bar_link'] ?? ''));
        $this->saveSetting($request, 'promo_bar_active', $request->boolean('promo_bar_active') ? '1' : '0');

        return redirect()->route('office.marketing.index')->with('status', 'Pengaturan marketing disimpan.');
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
            ['key' => 'rate_rmb',                   'group' => 'pricing',  'type' => 'string', 'value' => '3000',      'description' => 'Rate RMB ke IDR'],
            ['key' => 'profit_percent',              'group' => 'pricing',  'type' => 'string', 'value' => '11',        'description' => 'Profit persen'],
            ['key' => 'ongkir_cn_batam_per_kg',      'group' => 'pricing',  'type' => 'string', 'value' => '25000',     'description' => 'Ongkir CN ke Batam per kg'],
            ['key' => 'ongkir_batam_jakarta_per_kg', 'group' => 'pricing',  'type' => 'string', 'value' => '60000',     'description' => 'Ongkir Batam ke Jakarta per kg'],
            ['key' => 'default_weight_gram',         'group' => 'pricing',  'type' => 'string', 'value' => '0',         'description' => 'Default berat produk gram'],
            ['key' => 'fee_toco_fp',                 'group' => 'pricing',  'type' => 'string', 'value' => '1',         'description' => 'Fee Toco FP'],
            ['key' => 'fee_toco_dp',                 'group' => 'pricing',  'type' => 'string', 'value' => '0',         'description' => 'Fee Toco DP'],
            ['key' => 'fee_shopee_fp',               'group' => 'pricing',  'type' => 'string', 'value' => '109',       'description' => 'Fee Shopee FP'],
            ['key' => 'fee_shopee_dp',               'group' => 'pricing',  'type' => 'string', 'value' => '25',        'description' => 'Fee Shopee DP persen'],
            ['key' => 'fee_qris_percent',            'group' => 'pricing',  'type' => 'string', 'value' => '0.03',      'description' => 'Fee QRIS persen'],
            ['key' => 'qris_threshold',              'group' => 'pricing',  'type' => 'string', 'value' => '500000',    'description' => 'Threshold QRIS'],
            ['key' => 'dp_minimum_percent',          'group' => 'pricing',  'type' => 'string', 'value' => '50',        'description' => 'DP minimum persen'],

            // ── Email (DS 15.2) ──
            ['key' => 'customer_emails_paused',      'group' => 'email',    'type' => 'bool',   'value' => '0',         'description' => 'Stop kirim email ke customer'],
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
        ];

        foreach ($defaults as $d) {
            Setting::firstOrCreate(
                ['key' => $d['key']],
                [
                    'value'       => $d['value'],
                    'type'        => $d['type'],
                    'group'       => $d['group'],
                    'description' => $d['description'],
                ]
            );
        }
    }

    /**
     * Validasi per key — lebih spesifik untuk field penting.
     */
    private function rules(array $input): array
    {
        $rules = [];

        foreach ($input as $key => $value) {
            $rules[$key] = match (true) {
                // Bool fields
                in_array($key, [
                    'customer_emails_paused', 'notif_admin_bukti_bayar',
                    'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
                ]) => ['required', 'in:0,1'],

                // Numeric / price fields
                in_array($key, [
                    'rate_rmb', 'profit_percent',
                    'ongkir_cn_batam_per_kg', 'ongkir_batam_jakarta_per_kg',
                    'default_weight_gram', 'fee_toco_fp', 'fee_toco_dp',
                    'fee_shopee_fp', 'fee_shopee_dp', 'fee_qris_percent',
                    'qris_threshold', 'dp_minimum_percent', 'watermark_opacity',
                ]) => ['required', 'numeric', 'min:0'],

                // Percentage fields (0-100)
                in_array($key, ['profit_percent', 'fee_qris_percent', 'dp_minimum_percent', 'watermark_opacity'])
                    => ['required', 'numeric', 'min:0', 'max:100'],

                // Email fields
                in_array($key, ['email_security', 'email_notification', 'email_cs'])
                    => ['nullable', 'email', 'max:255'],

                // Watermark pattern
                $key === 'watermark_pattern'
                    => ['nullable', 'string', 'in:diagonal_tiled,center,bottom_right,corner_4'],

                // Email stop reason (textarea)
                $key === 'email_stop_reason'
                    => ['nullable', 'string', 'max:1000'],

                // Watermark text
                $key === 'watermark_text'
                    => ['nullable', 'string', 'max:255'],

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
            'customer_emails_paused', 'notif_admin_bukti_bayar',
            'anti_download_enabled', 'auto_watermark_enabled', 'heavy_watermark',
        ])) return 'bool';

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
            str_starts_with($key, 'dp_')     => 'pricing',

            str_starts_with($key, 'email_'), str_starts_with($key, 'notif_'),
            $key === 'customer_emails_paused' => 'email',

            str_starts_with($key, 'watermark_'), str_starts_with($key, 'anti_'),
            str_starts_with($key, 'auto_watermark'), $key === 'heavy_watermark'
                => 'security',

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

    private function saveSetting(Request $request, string $key, string $value): void
    {
        $setting = Setting::firstOrCreate(['key' => $key], [
            'value'       => $value,
            'type'        => 'string',
            'group'       => 'marketing',
            'description' => 'Pengaturan admin',
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