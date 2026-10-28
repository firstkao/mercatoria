<?php

use App\Models\MerchCategory;
use App\Services\Product\MerchCategoryService;
use Database\Seeders\MerchCategorySeeder;

test('merch category seeder mengisi 8 kategori prd 14', function () {
    $this->seed(MerchCategorySeeder::class);

    expect(MerchCategory::orderBy('priority')->pluck('slug')->all())->toBe([
        'shikishi',
        'standee',
        'pendant',
        'plush',
        'keychain',
        'badge',
        'apparel',
        'other',
    ]);
});

test('auto group mengikuti prioritas keyword prd 14', function () {
    $this->seed(MerchCategorySeeder::class);
    $service = app(MerchCategoryService::class);

    expect($service->resolveSlug('Genshin Impact Shikishi Art Board'))->toBe('shikishi')
        ->and($service->resolveSlug('Acrylic Stand Tartaglia'))->toBe('standee')
        ->and($service->resolveSlug('Akrilik Standee Alhaitham'))->toBe('standee')
        ->and($service->resolveSlug('Acrylic Pendant Klee'))->toBe('pendant')
        ->and($service->resolveSlug('Plushie Boneka Paimon'))->toBe('plush')
        ->and($service->resolveSlug('Nuigurumi Doll Xiao'))->toBe('plush')
        ->and($service->resolveSlug('Keychain Gantungan Kunci'))->toBe('keychain')
        ->and($service->resolveSlug('Acrylic Charm Venti'))->toBe('keychain')
        ->and($service->resolveSlug('Can Badge Set'))->toBe('badge')
        ->and($service->resolveSlug('T-Shirt Kaos Hoodie'))->toBe('apparel')
        ->and($service->resolveSlug('Mug Keramik Cantik'))->toBe('other');
});

test('prioritas keyword didahulukan dari atas ke bawah', function () {
    $this->seed(MerchCategorySeeder::class);
    $service = app(MerchCategoryService::class);

    // "stand" ada di Standee (prioritas 2); nama tidak memicu prioritas lain
    expect($service->resolveSlug('Standee Shikishi'))->toBe('shikishi');
});

test('keyword tidak match jatuh ke other', function () {
    $this->seed(MerchCategorySeeder::class);
    $service = app(MerchCategoryService::class);

    expect($service->resolveSlug('Produk Aneh Tanpa Keyword'))->toBe('other');
});
