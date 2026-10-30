<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Setting;
use App\Models\User;

test('tombol wa: blocked selalu tampil, crush saat kuota habis, memakai prefill', function () {
    Setting::create(['key' => 'url_whatsapp', 'value' => 'https://wa.me/628111', 'type' => 'string', 'group' => 'social']);

    // Blocked: akun terkunci -> tombol WA selalu tampil dengan pesan unblock (PRD 7.20).
    $blocked = User::factory()->create();
    Customer::factory()->create(['user_id' => $blocked->id, 'current_state' => 'Blocked']);

    $this->actingAs($blocked)->get('/dashboard')->assertOk()
        ->assertSee(rawurlencode('Halo Admin, saya mau minta unblock akun.'), false);

    // Crush kuota habis: tombol WA tampil dengan pesan reset view (PRD 7.20).
    $crush = User::factory()->create(['role' => User::ROLE_CRUSH]);
    foreach (Product::factory()->count(10)->create() as $product) {
        ProductView::create(['user_id' => $crush->id, 'product_id' => $product->id, 'viewed_at' => now()]);
    }

    $this->actingAs($crush)->get('/dashboard')->assertOk()
        ->assertSee(rawurlencode('Halo Admin, saya mau minta reset view produk.'), false);

    // Crush kuota masih ada: tombol WA belum tampil.
    $crushBaru = User::factory()->create(['role' => User::ROLE_CRUSH]);

    $this->actingAs($crushBaru)->get('/dashboard')->assertOk()
        ->assertDontSee(rawurlencode('Halo Admin, saya mau minta reset view produk.'), false);
});
