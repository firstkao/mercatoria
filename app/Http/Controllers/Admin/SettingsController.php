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
    private const OPTIONAL_SETTINGS = [
        'pause_customer_emails',
        'image_protection_enabled',
        'image_watermark_enabled',
        'image_watermark_opacity',
    ];

    private const SETTINGS = [
        'ongkir_cn_btm_per_kg' => [
            'type' => 'int',
            'group' => 'pricing',
            'description' => 'Ongkir CN → Batam per kg',
        ],
        'ongkir_btm_jkt_per_kg' => [
            'type' => 'int',
            'group' => 'pricing',
            'description' => 'Ongkir Batam → Jakarta per kg',
        ],
        'default_weight_gram' => [
            'type' => 'int',
            'group' => 'pricing',
            'description' => 'Default berat produk (gram)',
        ],
        'pause_customer_emails' => [
            'type' => 'bool',
            'group' => 'email',
            'description' => 'Stop kirim email ke customer (mode testing/maintenance)',
        ],
        'image_protection_enabled' => [
            'type' => 'bool',
            'group' => 'security',
            'description' => 'Proteksi klik-kanan, drag, dan long-press pada gambar',
        ],
        'image_watermark_enabled' => [
            'type' => 'bool',
            'group' => 'security',
            'description' => 'Watermark otomatis pada foto yang diunggah',
        ],
        'image_watermark_opacity' => [
            'type' => 'int',
            'group' => 'security',
            'description' => 'Opacity watermark (%)',
        ],
        'ga4_measurement_id' => [
            'type' => 'string',
            'group' => 'seo',
            'description' => 'GA4 Measurement ID',
        ],
    ];

    public function edit(): View
    {
        $settings = Setting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ongkir_cn_btm_per_kg' => ['required', 'integer', 'min:0', 'max:999999999'],
            'ongkir_btm_jkt_per_kg' => ['required', 'integer', 'min:0', 'max:999999999'],
            'default_weight_gram' => ['required', 'integer', 'min:0', 'max:999999'],
            'ga4_measurement_id' => ['nullable', 'string', 'regex:/\AG-[A-Z0-9]{4,20}\z/'],
            'pause_customer_emails' => ['sometimes', 'boolean'],
            'image_protection_enabled' => ['sometimes', 'boolean'],
            'image_watermark_enabled' => ['sometimes', 'boolean'],
            'image_watermark_opacity' => ['sometimes', 'integer', 'min:5', 'max:80'],
        ]);

        DB::transaction(function () use ($validated): void {
            $old = Setting::query()
                ->whereIn('key', array_keys(self::SETTINGS))
                ->pluck('value', 'key')
                ->all();
            $new = [];

            foreach (self::SETTINGS as $key => $metadata) {
                if (! array_key_exists($key, $validated) && in_array($key, self::OPTIONAL_SETTINGS, true)) {
                    continue;
                }
                $value = $validated[$key] ?? '';
                if ($metadata['type'] === 'bool') {
                    $value = $value ? '1' : '0';
                }
                $setting = Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [...$metadata, 'value' => (string) $value],
                );
                cache()->forget("setting.{$setting->key}");
                $new[$key] = (string) $value;
            }

            AdminLog::record(
                'settings_updated',
                Setting::class,
                null,
                $old,
                $new,
                'Admin memperbarui pengaturan ongkir dan GA4.',
            );
        });

        return back()->with('success', 'Pengaturan pengiriman dan GA4 berhasil disimpan.');
    }
}
