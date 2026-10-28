<?php

use App\Models\Setting;
use App\Models\User;

test('admin mengubah nilai setting', function () {
    $admin = User::factory()->admin()->create();
    Setting::create(['key' => 'profit_percent', 'value' => '11', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    $this->actingAs($admin)->post('/office/settings', [
        'settings' => ['profit_percent' => '12'],
    ])->assertRedirect();

    expect(setting('profit_percent'))->toBe('12');
});

test('halaman settings menampilkan nilai terkini', function () {
    $admin = User::factory()->admin()->create();
    Setting::create(['key' => 'rate_rmb_idr', 'value' => '3000', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    $this->actingAs($admin)->get('/office/settings')
        ->assertOk()
        ->assertSee('rate_rmb_idr')
        ->assertSee('3000');
});

test('update settings memakai validasi tipe', function () {
    $admin = User::factory()->admin()->create();
    Setting::create(['key' => 'catalog_per_page', 'value' => '30', 'type' => 'int', 'group' => 'display', 'description' => 'test']);

    $this->actingAs($admin)->post('/office/settings', [
        'settings' => ['catalog_per_page' => 'bukan-angka'],
    ])->assertSessionHasErrors('settings.catalog_per_page');
});
