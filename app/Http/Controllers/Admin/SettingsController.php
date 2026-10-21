<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public const TABS = [
        'umum' => 'Umum',
        'harga' => 'Harga & Pengiriman',
        'koin' => 'Koin',
        'email' => 'Email',
        'state' => 'State',
        'security' => 'Security',
        'seo' => 'SEO',
        'api' => 'API',
    ];

    /** key => [type, group, description, validation rules]; only keys present in the request are saved. */
    private const SETTINGS = [
        'rate_rmb_idr' => ['float', 'pricing', 'Rate RMB → IDR', ['numeric', 'min:1', 'max:999999']],
        'profit_percent' => ['float', 'pricing', 'Profit (%)', ['numeric', 'min:0', 'max:1000']],
        'ongkir_cn_btm_per_kg' => ['int', 'pricing', 'Ongkir CN → Batam per kg', ['integer', 'min:0', 'max:999999999']],
        'ongkir_btm_jkt_per_kg' => ['int', 'pricing', 'Ongkir Batam → Jakarta per kg', ['integer', 'min:0', 'max:999999999']],
        'default_weight_gram' => ['int', 'pricing', 'Default berat produk (gram)', ['integer', 'min:0', 'max:999999']],
        'fee_toco_fp' => ['int', 'pricing', 'Fee Toco Full Payment', ['integer', 'min:0', 'max:999999999']],
        'fee_toco_dp' => ['int', 'pricing', 'Fee Toco DP', ['integer', 'min:0', 'max:999999999']],
        'fee_shopee_fp' => ['int', 'pricing', 'Fee Shopee Full Payment', ['integer', 'min:0', 'max:999999999']],
        'fee_shopee_dp_percent' => ['float', 'pricing', 'Fee Shopee DP (%)', ['numeric', 'min:0', 'max:100']],
        'fee_qris_percent' => ['float', 'pricing', 'Fee QRIS (%)', ['numeric', 'min:0', 'max:100']],
        'qris_threshold' => ['int', 'pricing', 'Threshold fee QRIS (Rp)', ['integer', 'min:0', 'max:999999999']],
        'dp_minimum_percent' => ['int', 'pricing', 'DP minimum (%)', ['integer', 'min:1', 'max:100']],
        'pause_customer_emails' => ['bool', 'email', 'Stop kirim email ke customer (mode testing/maintenance)', ['boolean']],
        'notif_admin_payment_proof' => ['bool', 'email', 'Notifikasi email admin saat bukti bayar masuk', ['boolean']],
        'image_protection_enabled' => ['bool', 'security', 'Proteksi klik-kanan, drag, dan long-press pada gambar', ['boolean']],
        'image_watermark_enabled' => ['bool', 'security', 'Watermark otomatis pada foto yang diunggah', ['boolean']],
        'image_watermark_text' => ['string', 'security', 'Teks watermark', ['nullable', 'string', 'max:60']],
        'image_watermark_pattern' => ['string', 'security', 'Pola watermark', ['in:diagonal_tiled,center,corner']],
        'image_watermark_opacity' => ['int', 'security', 'Opacity watermark (%)', ['integer', 'min:5', 'max:80']],
        'image_watermark_heavy_direct' => ['bool', 'security', 'Watermark tebal saat gambar dibuka langsung', ['boolean']],
        'ga4_measurement_id' => ['string', 'seo', 'GA4 Measurement ID', ['nullable', 'string', 'regex:/\AG-[A-Z0-9]{4,20}\z/']],
    ];

    public function edit(Request $request): View
    {
        $tab = array_key_exists((string) $request->query('tab'), self::TABS) ? $request->query('tab') : 'umum';
        $settings = Setting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->pluck('value', 'key');

        return view('admin.settings.edit', [
            'settings' => $settings,
            'tab' => $tab,
            'tabs' => self::TABS,
            'emailPaused' => (bool) ($settings->get('pause_customer_emails') ?? false) && $settings->get('pause_customer_emails') !== '0',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = ['pause_reason' => ['nullable', 'string', 'max:500']];
        foreach (self::SETTINGS as $key => [, , , $keyRules]) {
            $rules[$key] = ['sometimes', ...$keyRules];
        }
        $rules['ongkir_cn_btm_per_kg'][] = 'required';
        $rules['ongkir_btm_jkt_per_kg'][] = 'required';
        $rules['default_weight_gram'][] = 'required';
        $validated = $request->validate($rules);

        $events = [];
        DB::transaction(function () use ($validated, &$events): void {
            $old = Setting::query()->whereIn('key', array_keys(self::SETTINGS))->pluck('value', 'key')->all();
            $new = [];

            foreach (self::SETTINGS as $key => [$type, $group, $description]) {
                if (! array_key_exists($key, $validated)) {
                    continue;
                }
                $value = $validated[$key] ?? '';
                if ($type === 'bool') {
                    $value = $value ? '1' : '0';
                }
                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    ['type' => $type, 'group' => $group, 'description' => $description, 'value' => (string) $value],
                );
                cache()->forget("setting.{$key}");
                $new[$key] = (string) $value;
            }

            $changed = array_filter($new, fn ($v, $k) => ($old[$k] ?? null) !== $v, ARRAY_FILTER_USE_BOTH);
            $reason = trim((string) ($validated['pause_reason'] ?? ''));

            if (array_key_exists('pause_customer_emails', $changed)) {
                $enabled = $changed['pause_customer_emails'] === '1';
                AdminLog::record(
                    'toggle_customer_email',
                    Setting::class,
                    null,
                    ['pause_customer_emails' => $old['pause_customer_emails'] ?? '0'],
                    ['pause_customer_emails' => $changed['pause_customer_emails'], 'reason' => $reason],
                    ($enabled ? 'Admin menghentikan email customer' : 'Admin mengaktifkan kembali email customer')
                        .($reason !== '' ? ": {$reason}" : '.'),
                );
                $events[] = ['name' => 'admin_toggle_customer_email', 'params' => ['enabled' => $enabled]];
            }

            AdminLog::record(
                'settings_updated',
                Setting::class,
                null,
                array_intersect_key($old, $new),
                $new,
                'Admin memperbarui pengaturan: '.(implode(', ', array_keys($changed)) ?: 'tanpa perubahan').'.',
            );
        });

        $redirect = back()->with('success', 'Pengaturan berhasil disimpan.');

        return $events ? $redirect->with('ga4_events', $events) : $redirect;
    }
}
