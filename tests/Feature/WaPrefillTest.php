<?php

use App\Models\Customer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('waUrlPrefill memuat pesan sesuai state crush dan blocked', function () {
    Setting::create(['key' => 'url_whatsapp', 'value' => 'https://wa.me/6281234567890', 'type' => 'string', 'group' => 'social', 'description' => 'test']);
    Cache::forget('app_settings_all');

    $crush = User::factory()->create(['role' => 'crush']);
    $this->actingAs($crush)->get('/dashboard')->assertOk()
        ->assertViewHas('waUrlPrefill', fn ($url) => is_string($url)
            && str_contains($url, 'https://wa.me/6281234567890?text=')
            && str_contains($url, rawurlencode('Halo Admin, saya mau minta reset view produk.')));

    $blocked = User::factory()->create();
    Customer::factory()->create(['user_id' => $blocked->id, 'current_state' => 'blocked']);
    $this->actingAs($blocked)->get('/dashboard')->assertOk()
        ->assertViewHas('waUrlPrefill', fn ($url) => is_string($url)
            && str_contains($url, 'https://wa.me/6281234567890?text=')
            && str_contains($url, rawurlencode('Halo Admin, saya mau minta unblock akun.')));
});
