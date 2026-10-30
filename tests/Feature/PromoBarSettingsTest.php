<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Cache;

test('settings promo bar punya text, discount, dan min_purchase', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::where('key', 'promo_bar_text')->value('value'))
        ->toContain('promo NEWWEB')
        ->and(Setting::where('key', 'promo_bar_discount')->value('value'))->toBe('25000')
        ->and(Setting::where('key', 'promo_bar_min_purchase')->value('value'))->toBe('150000');
});

test('nilai promo bar discount dan min purchase bisa diubah admin', function () {
    $this->seed(SettingsSeeder::class);

    Setting::where('key', 'promo_bar_discount')->update(['value' => '30000']);
    Setting::where('key', 'promo_bar_min_purchase')->update(['value' => '200000']);
    Cache::forget('app_settings_all');

    expect(setting('promo_bar_discount'))->toBe('30000')
        ->and(setting('promo_bar_min_purchase'))->toBe('200000');
});
