<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

test('promo bar permanen dengan format NEWWEB bold italic amber', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('promo-bar')
        ->toContain('Gunakan kode promo')
        ->toContain('<strong><em><span class="text-[#D97706]">NEWWEB</span></em></strong>', false)
        ->toContain('dan nikmati potongan harga untuk belanja pertamamu.');
});

test('tidak ada setting promo bar yang tersimpan', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::where('key', 'like', 'promo_bar%')->exists())->toBeFalse();
});
