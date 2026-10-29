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
        return view('admin.settings.index', [
            'groups' => Setting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $input = (array) $request->input('settings', []);
        $settings = Setting::whereIn('key', array_keys($input))->get()->keyBy('key');

        $validator = validator($input, $this->rules($settings));

        if ($validator->fails()) {
            // Remap error key ke settings.{key} agar mudah ditampilkan.
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                foreach ($messages as $message) {
                    $errors["settings.{$key}"][] = $message;
                }
            }
            throw ValidationException::withMessages($errors);
        }

        foreach ($settings as $key => $setting) {
            $oldValue = $setting->value;
            $setting->update(['value' => (string) $input[$key]]);

            if ($oldValue !== (string) $input[$key]) {
                AdminLog::record($request->user(), 'update', 'Setting', $setting->id,
                    ['value' => $oldValue], ['value' => (string) $input[$key]],
                    "Ubah setting {$key}");
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
            'promo_bar_text' => (string) setting('promo_bar_text', ''),
            'promo_bar_link' => (string) setting('promo_bar_link', ''),
            'promo_bar_active' => (string) setting('promo_bar_active', '1'),
        ]);
    }

    public function updateMarketing(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promo_bar_text' => ['nullable', 'string', 'max:500'],
            'promo_bar_link' => ['nullable', 'string', 'max:255'],
            'promo_bar_active' => ['nullable', 'boolean'],
        ]);

        $this->saveSetting($request, 'promo_bar_text', (string) ($data['promo_bar_text'] ?? ''));
        $this->saveSetting($request, 'promo_bar_link', (string) ($data['promo_bar_link'] ?? ''));
        $this->saveSetting($request, 'promo_bar_active', $request->boolean('promo_bar_active') ? '1' : '0');

        return redirect()->route('office.marketing.index')->with('status', 'Pengaturan marketing disimpan.');
    }

    private function saveSetting(Request $request, string $key, string $value): void
    {
        $setting = Setting::firstOrCreate(['key' => $key], [
            'value' => $value,
            'type' => 'string',
            'group' => 'marketing',
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

    /**
     * @param  Collection<string, Setting>  $settings
     * @return array<string, array<int, string>>
     */
    private function rules($settings): array
    {
        $rules = [];

        foreach ($settings as $key => $setting) {
            $rules[$key] = match ($setting->type) {
                'int' => ['required', 'integer'],
                'bool' => ['required', 'in:0,1'],
                default => ['nullable', 'string', 'max:500'],
            };
        }

        return $rules;
    }
}
