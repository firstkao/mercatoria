<?php

use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\User;

test('keyword kategori tersimpan sebagai array', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/merch-categories', [
        'name' => 'Standee',
        'priority' => 2,
        'keywords' => 'standee, acrylic stand, akrilik',
    ])->assertRedirect();

    $category = MerchCategory::where('slug', 'standee')->firstOrFail();
    expect($category->keywords)->toBe(['standee', 'acrylic stand', 'akrilik']);
});

test('produk otomatis ter-group by keyword sesuai prioritas dan bisa di-override manual', function () {
    $admin = User::factory()->admin()->create();

    MerchCategory::factory()->create(['name' => 'Shikishi', 'slug' => 'shikishi', 'priority' => 1, 'keywords' => ['shikishi', 'standee']]);
    MerchCategory::factory()->create(['name' => 'Standee', 'slug' => 'standee', 'priority' => 2, 'keywords' => ['standee', 'akrilik']]);

    // 'standee' cocok di dua kategori -> prioritas 1 (Shikishi) menang
    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'name' => 'Acrylic Standee Limited',
    ]))->assertRedirect();

    $auto = Product::where('name', 'Acrylic Standee Limited')->firstOrFail();
    expect($auto->merch_category_id)->toBe(MerchCategory::where('slug', 'shikishi')->firstOrFail()->id);

    // Override manual di level produk tetap menang
    $manualId = MerchCategory::where('slug', 'standee')->firstOrFail()->id;
    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'name' => 'Acrylic Standee Manual',
        'merch_category_id' => $manualId,
    ]))->assertRedirect();

    $manual = Product::where('name', 'Acrylic Standee Manual')->firstOrFail();
    expect($manual->merch_category_id)->toBe($manualId);
});
