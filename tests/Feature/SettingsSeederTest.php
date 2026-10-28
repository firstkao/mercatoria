<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

test('settings seeder mengisi default sesuai prd section 23', function () {
    $this->seed(SettingsSeeder::class);

    $expected = [
        'rate_rmb_idr' => '3000',
        'profit_percent' => '11',
        'ongkir_cn_btm_per_kg' => '25000',
        'ongkir_btm_jkt_per_kg' => '60000',
        'default_weight_gram' => '0',
        'fee_toco_fp' => '1',
        'fee_toco_dp' => '0',
        'fee_shopee_fp' => '109',
        'fee_shopee_dp' => '25',
        'fee_qris_percent' => '0.03',
        'qris_threshold' => '500000',
        'dp_minimum_percent' => '50',
        'coin_max_discount_percent' => '5',
        'coin_expiry_days' => '365',
        'auto_delete_crush_days' => '30',
        'view_limit' => '10',
        'max_register_without_purchase' => '3',
        'email_verification_hours' => '24',
        'login_rate_limit_per_minute' => '5',
        'login_lockout_minutes' => '15',
        'unblock_grace_days' => '7',
        'pause_customer_emails' => '0',
        'image_protection_enabled' => '1',
        'image_watermark_enabled' => '1',
        'image_watermark_opacity' => '25',
        'late_penalty_per_day' => '1000',
        'tier_gold_threshold' => '2000000',
        'tier_platinum_threshold' => '5000000',
    ];

    foreach ($expected as $key => $value) {
        expect(Setting::where('key', $key)->value('value'))->toBe($value);
    }
});

test('settings seeder dapat dijalankan ulang tanpa duplikat', function () {
    $this->seed(SettingsSeeder::class);
    $count = Setting::count();

    $this->seed(SettingsSeeder::class);

    expect(Setting::count())->toBe($count);
});
