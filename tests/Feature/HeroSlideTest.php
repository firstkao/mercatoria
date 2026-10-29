<?php

use App\Models\AdminLog;
use App\Models\HeroSlide;
use App\Models\User;

test('hero slide aktif tampil di landing dengan struktur image url', function () {
    HeroSlide::factory()->create([
        'image' => '/images/hero-promo.jpg',
        'url' => '/promo/merch-sale',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $html = $this->get('/')->getContent();

    expect($html)->toContain('/images/hero-promo.jpg')
        ->toContain('/promo/merch-sale');
});

test('hero slide nonaktif tidak tampil di landing', function () {
    HeroSlide::factory()->create([
        'image' => '/images/hero-mati.jpg',
        'url' => '/promo-mati',
        'is_active' => false,
    ]);

    $html = $this->get('/')->getContent();

    expect($html)->not->toContain('/images/hero-mati.jpg');
});

test('controller landing mengirim heroSlides berstruktur image url terurut', function () {
    HeroSlide::factory()->create(['image' => '/img/kedua.jpg', 'url' => '/a', 'sort_order' => 2, 'is_active' => true]);
    HeroSlide::factory()->create(['image' => '/img/pertama.jpg', 'url' => '/b', 'sort_order' => 1, 'is_active' => true]);

    $response = $this->get('/')->assertOk();
    $heroSlides = collect($response->viewData('heroSlides'));

    // Contract: kumpulan {image, url} terurut sort_order
    expect($heroSlides->pluck('image')->all())->toBe(['/img/pertama.jpg', '/img/kedua.jpg'])
        ->and($heroSlides->pluck('url')->all())->toBe(['/b', '/a']);
});

test('admin dapat membuat mengubah dan menghapus hero slide', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/hero-slides', [
        'image' => '/images/hero-1.jpg',
        'url' => '/promo-1',
        'sort_order' => 1,
        'is_active' => 1,
    ])->assertRedirect();

    $slide = HeroSlide::where('image', '/images/hero-1.jpg')->firstOrFail();

    $this->actingAs($admin)->put("/office/hero-slides/{$slide->id}", [
        'image' => '/images/hero-1-baru.jpg',
        'url' => '/promo-1-baru',
        'sort_order' => 2,
        'is_active' => 1,
    ])->assertRedirect();

    expect($slide->fresh()->image)->toBe('/images/hero-1-baru.jpg');

    $this->actingAs($admin)->delete("/office/hero-slides/{$slide->id}")->assertRedirect();

    expect(HeroSlide::whereKey($slide->id)->exists())->toBeFalse();
});

test('kelola hero slide tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/hero-slides', [
        'image' => '/images/hero-log.jpg',
        'url' => '/',
        'is_active' => 1,
    ]);

    $slide = HeroSlide::where('image', '/images/hero-log.jpg')->firstOrFail();

    $this->actingAs($admin)->delete("/office/hero-slides/{$slide->id}");

    expect(AdminLog::where('model', 'HeroSlide')->where('action', 'create')->exists())->toBeTrue()
        ->and(AdminLog::where('model', 'HeroSlide')->where('action', 'delete')->exists())->toBeTrue();
});

test('non-admin ditolak 403 kelola hero slide', function () {
    $customer = User::factory()->create();
    $slide = HeroSlide::factory()->create();

    $this->actingAs($customer)->post('/office/hero-slides', ['image' => '/x.jpg'])->assertForbidden();
    $this->actingAs($customer)->put("/office/hero-slides/{$slide->id}", ['image' => '/x.jpg'])->assertForbidden();
    $this->actingAs($customer)->delete("/office/hero-slides/{$slide->id}")->assertForbidden();

    expect(HeroSlide::whereKey($slide->id)->exists())->toBeTrue();
});

test('image hero slide wajib diisi', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/hero-slides', ['url' => '/'])
        ->assertSessionHasErrors('image');
});
