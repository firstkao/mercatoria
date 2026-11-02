<?php

use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('MerchCategory::navList di-cache dan di-flush saat saved/deleted', function () {
    $category = MerchCategory::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'priority' => 1]);

    expect(MerchCategory::navList()->pluck('name')->all())->toBe(['Alpha'])
        ->and(Cache::has(MerchCategory::NAV_CACHE_KEY))->toBeTrue();

    MerchCategory::factory()->create(['name' => 'Beta', 'slug' => 'beta', 'priority' => 2]);
    expect(MerchCategory::navList()->pluck('name')->all())->toBe(['Alpha', 'Beta']);

    $category->delete();
    expect(MerchCategory::navList()->pluck('name')->all())->toBe(['Beta']);
});

test('Tag::navList di-cache dan di-flush saat saved/deleted', function () {
    $tag = Tag::create(['name' => 'Zed', 'slug' => 'zed']);
    expect(Tag::navList()->pluck('name')->all())->toBe(['Zed']);

    Tag::create(['name' => 'Abe', 'slug' => 'abe']);
    expect(Tag::navList()->pluck('name')->all())->toBe(['Abe', 'Zed']);

    $tag->delete();
    expect(Tag::navList()->pluck('name')->all())->toBe(['Abe']);
});

test('GET /office/merch-categories tidak menulis ke database', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Gelang Cantik', 'is_active' => true]);

    $this->actingAs($admin)->get('/office/merch-categories')->assertOk();

    expect(MerchCategory::count())->toBe(0);
});

test('auto-generate kategori lewat POST dan command', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Gelang Cantik', 'is_active' => true]);

    $this->actingAs($admin)->post('/office/merch-categories/auto-generate')->assertRedirect(route('office.merch-categories.index'));
    expect(MerchCategory::where('name', 'Gelang')->exists())->toBeTrue();

    Product::factory()->create(['name' => 'Kalung Indah', 'is_active' => true]);
    $this->artisan('merch:auto-categories')->assertSuccessful();
    expect(MerchCategory::where('name', 'Kalung')->exists())->toBeTrue();
});

test('settings menolak key yang tidak ada di whitelist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/settings', ['settings' => ['key_ngawur' => 'x']])
        ->assertSessionHasErrors('settings.key_ngawur');
});
