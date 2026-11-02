<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

test('promo bar permanen dengan NEWWEB merah bold dan nominal bold italic', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('promo-bar')
        ->toContain('Gunakan kode promo')
        ->toContain('<strong class="text-danger">NEWWEB</strong>', false)
        ->toContain('<strong><em>Rp 25.000</em></strong>', false)
        ->toContain('dengan minimal pembelanjaan')
        ->toContain('<strong><em>Rp 150.000</em></strong>', false);
});

test('tidak ada setting promo bar yang tersimpan', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::where('key', 'like', 'promo_bar%')->exists())->toBeFalse();
});
