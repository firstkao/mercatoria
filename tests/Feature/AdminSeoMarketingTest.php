<?php

use App\Models\AdminLog;
use App\Models\User;
use Database\Seeders\SettingsSeeder;

test('admin simpan pengaturan seo', function () {
    $this->seed(SettingsSeeder::class);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/seo', [
        'ga4_measurement_id' => 'G-TEST123456',
    ])->assertRedirect();

    expect(setting('ga4_measurement_id'))->toBe('G-TEST123456')
        ->and(AdminLog::where('action', 'update')->where('model', 'Setting')->exists())->toBeTrue();
});

test('non-admin ditolak simpan pengaturan seo', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post('/office/seo', ['ga4_measurement_id' => 'G-X'])->assertForbidden();
});
