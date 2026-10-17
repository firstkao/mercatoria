<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    private const SETTINGS = [
        'seo_default_title' => 'Mercatoria — Marketplace Merchandise IP Cina',
        'seo_default_description' => 'Temukan dan beli merchandise game, anime, donghua, dan novel dari Tiongkok melalui Mercatoria.',
        'seo_default_og_image' => '',
    ];

    public function edit(): View
    {
        $settings = Setting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->pluck('value', 'key');

        return view('admin.seo.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seo_default_title' => ['required', 'string', 'max:255'],
            'seo_default_description' => ['required', 'string', 'max:500'],
            'seo_default_og_image' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        DB::transaction(function () use ($validated): void {
            $old = Setting::query()
                ->whereIn('key', array_keys(self::SETTINGS))
                ->pluck('value', 'key')
                ->all();
            $new = [];

            foreach (self::SETTINGS as $key => $default) {
                $value = $validated[$key] ?? $default;
                $setting = Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => (string) $value,
                        'type' => 'string',
                        'group' => 'seo',
                        'description' => match ($key) {
                            'seo_default_title' => 'SEO title default untuk halaman publik',
                            'seo_default_description' => 'SEO description default untuk halaman publik',
                            default => 'Gambar Open Graph default',
                        },
                    ],
                );
                cache()->forget("setting.{$setting->key}");
                $new[$key] = (string) $value;
            }

            AdminLog::record(
                'seo_settings_updated',
                Setting::class,
                null,
                old: $old,
                new: $new,
                description: 'Admin memperbarui metadata SEO global.',
            );
        });

        return back()->with('success', 'Pengaturan SEO berhasil disimpan.');
    }
}
