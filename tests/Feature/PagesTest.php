<?php

use App\Models\AdminLog;
use App\Models\Page;
use App\Models\User;

test('admin dapat membuat dan mengubah halaman cms', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/pages', [
        'title' => 'Tentang Kami',
        'slug' => 'tentang-kami',
        'content' => 'Kami adalah toko merch.',
        'is_active' => 1,
    ])->assertRedirect();

    $page = Page::where('slug', 'tentang-kami')->firstOrFail();

    $this->actingAs($admin)->put("/office/pages/{$page->id}", [
        'title' => 'Tentang Kami',
        'content' => 'Teks baru.',
        'is_active' => 1,
    ])->assertRedirect();

    expect($page->fresh()->content)->toBe('Teks baru.');
});

test('halaman cms aktif tampil di halaman publik', function () {
    Page::factory()->create([
        'slug' => 'tentang-kami',
        'title' => 'Tentang Kami',
        'content' => 'Kami adalah toko merch terpercaya.',
        'is_active' => true,
    ]);

    $this->get('/tentang-kami')
        ->assertOk()
        ->assertSee('Tentang Kami')
        ->assertSee('toko merch terpercaya');
});

test('halaman cms nonaktif tidak tampil', function () {
    Page::factory()->create([
        'slug' => 'faq',
        'title' => 'FAQ',
        'content' => 'Isi FAQ',
        'is_active' => false,
    ]);

    $this->get('/faq')->assertNotFound();
});

test('halaman publik tetap placeholder bila belum ada di cms', function () {
    $this->get('/cara-belanja')->assertOk()->assertSee('Menyusul');
});

test('perubahan halaman tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    $page = Page::factory()->create(['slug' => 'tnc', 'content' => 'Lama', 'is_active' => true]);

    $this->actingAs($admin)->put("/office/pages/{$page->id}", [
        'title' => $page->title,
        'content' => 'Baru',
        'is_active' => 1,
    ]);

    expect(AdminLog::where('model', 'Page')->exists())->toBeTrue();
});
