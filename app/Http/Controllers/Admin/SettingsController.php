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
