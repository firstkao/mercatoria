<?php

use App\Models\AdminLog;
use App\Models\User;
use Database\Seeders\SettingsSeeder;

test('admin simpan pengaturan seo dan marketing', function () {
    $this->seed(SettingsSeeder::class);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/seo', [
        'ga4_measurement_id' => 'G-TEST123456',
    ])->assertRedirect();

    expect(setting('ga4_measurement_id'))->toBe('G-TEST123456')
        ->and(AdminLog::where('action', 'update')->where('model', 'Setting')->exists())->toBeTrue();

    $this->actingAs($admin)->post('/office/marketing', [
        'promo_bar_text' => 'Promo Merdeka',
        'promo_bar_link' => '/promo',
        'promo_bar_active' => 1,
    ])->assertRedirect();

    expect(setting('promo_bar_text'))->toBe('Promo Merdeka')
        ->and(setting('promo_bar_link'))->toBe('/promo')
        ->and(setting('promo_bar_active'))->toBe('1');
});

test('non-admin ditolak simpan pengaturan seo dan marketing', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post('/office/seo', ['ga4_measurement_id' => 'G-X'])->assertForbidden();
    $this->actingAs($customer)->post('/office/marketing', ['promo_bar_text' => 'x'])->assertForbidden();
});
