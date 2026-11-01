<?php

use App\Models\EmailTemplate;
use App\Models\Page;
use App\Models\Spammer;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

// ---------- Stores ----------
test('UAT store CRUD: list, create, update, delete dengan flash message', function () {
    Store::factory()->create(['name' => 'Toko Awal']);
    $this->get('/office/stores')->assertOk()->assertSee('Toko Awal');

    $this->post('/office/stores', ['name' => 'Toko Baru & <Spesial>', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 10])
        ->assertRedirect('/office/stores')->assertSessionHas('status', 'Store dibuat.');

    $store = Store::where('name', 'Toko Baru & <Spesial>')->firstOrFail();
    $this->get('/office/stores')->assertOk()->assertSee('Toko Baru &amp; &lt;Spesial&gt;', false);

    $this->put("/office/stores/{$store->id}", ['name' => 'Toko Edit', 'min_belanja_rmb' => 200, 'ongkir_rmb' => 20])
        ->assertSessionHas('status', 'Store diperbarui.');
    expect($store->fresh()->name)->toBe('Toko Edit');
    $this->get('/office/stores')->assertOk()->assertSee('Toko Edit');

    $this->delete("/office/stores/{$store->id}")->assertSessionHas('status');
    $this->assertModelMissing($store);
});

test('UAT store validasi: kosong, negatif, dan terlalu panjang ditolak', function () {
    $this->post('/office/stores', [])->assertSessionHasErrors(['name', 'min_belanja_rmb', 'ongkir_rmb']);
    $this->post('/office/stores', ['name' => 'x', 'min_belanja_rmb' => -1, 'ongkir_rmb' => -1])
        ->assertSessionHasErrors(['min_belanja_rmb', 'ongkir_rmb']);
    $this->post('/office/stores', ['name' => str_repeat('a', 101), 'min_belanja_rmb' => 1, 'ongkir_rmb' => 1])
        ->assertSessionHasErrors('name');
    expect(Store::count())->toBe(0);
});

// ---------- Tags ----------
test('UAT tag CRUD + duplikat ditolak', function () {
    $this->post('/office/tags', ['name' => 'Anime'])->assertSessionHas('status', 'Tag dibuat.');
    $tag = Tag::where('name', 'Anime')->firstOrFail();
    expect($tag->slug)->toBe('anime');

    $this->post('/office/tags', ['name' => 'Anime'])->assertSessionHasErrors('name');
    $this->post('/office/tags', [])->assertSessionHasErrors('name');

    $this->get('/office/tags')->assertOk()->assertSee('Anime');
    $this->put("/office/tags/{$tag->id}", ['name' => 'Manga'])->assertSessionHasNoErrors();
    expect($tag->fresh()->name)->toBe('Manga');

    $this->delete("/office/tags/{$tag->id}");
    $this->assertModelMissing($tag);
});

// ---------- Users ----------
function uatUserPayload(array $o = []): array
{
    return array_merge([
        'name' => "Siti O'Neil",
        'email' => 'siti@example.com',
        'password' => 'password123',
        'no_hp' => '081234567890',
        'provinsi' => 'Jawa Barat', 'kota' => 'Bandung', 'kecamatan' => 'Coblong',
        'alamat' => 'Jl. Dago 1', 'kode_pos' => '40135',
        'role' => 'customer',
    ], $o);
}

test('UAT user CRUD: create, detail, update, delete', function () {
    $this->post('/office/users', uatUserPayload())->assertSessionHas('status', 'User dibuat.');
    $user = User::where('email', 'siti@example.com')->firstOrFail();

    $this->get('/office/users')->assertOk()->assertSee('siti@example.com');
    $this->get("/office/users/{$user->id}/edit")->assertOk()->assertSee('siti@example.com');

    $this->put("/office/users/{$user->id}", uatUserPayload(['name' => 'Siti Baru', 'password' => '']))
        ->assertSessionHas('status', 'User diperbarui.');
    expect($user->fresh()->name)->toBe('Siti Baru');

    $this->delete("/office/users/{$user->id}")->assertSessionHas('status', 'User dihapus permanen.');
    $this->assertModelMissing($user);
});

test('UAT user validasi: kosong, email duplikat, no_hp salah', function () {
    $this->post('/office/users', [])->assertSessionHasErrors(['name', 'email', 'no_hp', 'provinsi']);
    User::factory()->create(['email' => 'ada@example.com']);
    $this->post('/office/users', uatUserPayload(['email' => 'ada@example.com']))->assertSessionHasErrors('email');
    $this->post('/office/users', uatUserPayload(['no_hp' => '12345']))->assertSessionHasErrors('no_hp');
});

test('UAT user dengan order tidak bisa dihapus', function () {
    [$user] = placeTestOrder();

    $this->delete("/office/users/{$user->id}")->assertSessionHasErrors('delete');
    $this->assertModelExists($user);
});

test('UAT user search ?q= dan pagination 30 per halaman', function () {
    User::factory()->create(['name' => 'Zebra Uniqueperson', 'email' => 'zebra@example.com']);
    User::factory()->count(35)->create();

    $this->get('/office/users?q=Uniqueperson')->assertOk()->assertSee('zebra@example.com');
    $this->get('/office/users?q=tidak-ada-xyz')->assertOk()->assertDontSee('zebra@example.com');

    $page1 = $this->get('/office/users')->assertOk();
    expect(substr_count($page1->getContent(), '/edit"'))->toBeGreaterThan(0);
    $this->get('/office/users')->assertOk()->assertDontSee('zebra@example.com');
    $this->get('/office/users?page=2')->assertOk()->assertSee('zebra@example.com');
});

// ---------- Pages (CMS) ----------
test('UAT page CMS: draft lalu publish tampil publik', function () {
    $this->post('/office/pages', ['title' => 'Promo Akhir Tahun', 'slug' => 'promo-akhir', 'content' => '<p>Halo</p>', 'status' => 'draft'])
        ->assertSessionHasNoErrors();
    $page = Page::where('slug', 'promo-akhir')->firstOrFail();

    auth()->logout();
    $this->get('/promo-akhir')->assertNotFound();

    $this->actingAs($this->admin);
    $this->put("/office/pages/{$page->id}", ['title' => 'Promo Akhir Tahun', 'slug' => 'promo-akhir', 'content' => '<p>Halo</p>', 'status' => 'publish'])
        ->assertSessionHasNoErrors();

    auth()->logout();
    $this->get('/promo-akhir')->assertOk()->assertSee('Halo', false);
});

test('UAT page validasi: title kosong, status invalid, meta terlalu panjang', function () {
    $this->post('/office/pages', [])->assertSessionHasErrors('title');
    $this->post('/office/pages', ['title' => 'x', 'status' => 'aneh'])->assertSessionHasErrors('status');
    $this->post('/office/pages', ['title' => 'x', 'status' => 'draft', 'meta_description' => str_repeat('a', 301)])
        ->assertSessionHasErrors('meta_description');
});

// ---------- Spammers ----------
test('UAT spammer: tambah, validasi, unblock ke watchlist', function () {
    $this->post('/office/spammers', [])->assertSessionHasErrors(['email', 'no_hp']);
    $this->post('/office/spammers', ['email' => 'bad@example.com', 'no_hp' => '081111111111'])->assertSessionHasNoErrors();
    $s = Spammer::where('email', 'bad@example.com')->firstOrFail();
    $this->get('/office/spammers')->assertOk()->assertSee('bad@example.com');
    $this->post("/office/spammers/{$s->id}/unblock")->assertSessionHasNoErrors();
    expect($s->fresh()->status)->toBe(Spammer::STATUS_WATCHLIST)->and($s->fresh()->unblock_count)->toBe(1);
});

// ---------- Email templates ----------
test('UAT email template: create, validasi, update, delete', function () {
    $this->post('/office/email-templates', [])->assertSessionHasErrors(['code', 'subject', 'body']);
    $this->post('/office/email-templates', ['code' => 'uat_tpl', 'subject' => 'Halo', 'body' => 'Isi'])->assertSessionHasNoErrors();
    $t = EmailTemplate::where('code', 'uat_tpl')->firstOrFail();
    $this->post('/office/email-templates', ['code' => 'uat_tpl', 'subject' => 'X', 'body' => 'Y'])->assertSessionHasErrors('code');
    $this->get('/office/email-templates')->assertOk()->assertSee('uat_tpl');
    $this->put("/office/email-templates/{$t->id}", ['code' => 'uat_tpl', 'subject' => 'Baru', 'body' => 'Isi'])->assertSessionHasNoErrors();
    expect($t->fresh()->subject)->toBe('Baru');
    $this->delete("/office/email-templates/{$t->id}");
    $this->assertModelMissing($t);
});
