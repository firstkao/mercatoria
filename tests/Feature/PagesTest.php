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

test('konten cms panjang berformat html tersimpan utuh tanpa strip tags', function () {
    $admin = User::factory()->admin()->create();
    $content = '<h2>Judul Panduan</h2>'.str_repeat(
        '<p>Paragraf dengan <strong>tebal</strong>, <em>miring</em>, dan <a href="/tnc">tautan</a>.</p>',
        200
    );

    $this->actingAs($admin)->post('/office/pages', [
        'title' => 'Panduan Panjang',
        'slug' => 'panduan-panjang',
        'content' => $content,
        'is_active' => 1,
    ])->assertRedirect();

    expect(strlen($content))->toBeGreaterThan(10000)
        ->and(Page::where('slug', 'panduan-panjang')->firstOrFail()->content)->toBe($content);
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

test('admin dapat menghapus halaman cms', function () {
    $admin = User::factory()->admin()->create();
    $page = Page::factory()->create(['slug' => 'dihapus']);

    $this->actingAs($admin)->delete("/office/pages/{$page->id}")->assertRedirect();

    expect(Page::where('slug', 'dihapus')->exists())->toBeFalse();
});

test('penghapusan halaman tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    $page = Page::factory()->create(['slug' => 'dihapus-log']);

    $this->actingAs($admin)->delete("/office/pages/{$page->id}");

    expect(AdminLog::where('model', 'Page')->where('action', 'delete')->exists())->toBeTrue();
});

test('non-admin ditolak 403 menghapus halaman', function () {
    $customer = User::factory()->create();
    $page = Page::factory()->create();

    $this->actingAs($customer)->delete("/office/pages/{$page->id}")->assertForbidden();

    expect(Page::whereKey($page->id)->exists())->toBeTrue();
});

test('guest tidak bisa menghapus halaman', function () {
    $page = Page::factory()->create();

    $this->delete("/office/pages/{$page->id}")->assertRedirect('/login');

    expect(Page::whereKey($page->id)->exists())->toBeTrue();
});
