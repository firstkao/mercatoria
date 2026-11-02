<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\Order;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Models\Product;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\Product\ProductSearchService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('{slug}: halaman publish tampil, draft dan slug tak dikenal 404', function () {
    Page::factory()->create(['slug' => 'halaman-a', 'status' => 'publish', 'title' => 'Halaman A']);
    Page::factory()->create(['slug' => 'halaman-b', 'status' => 'draft']);

    $this->get('/halaman-a')->assertOk()->assertSee('Halaman A');
    $this->get('/halaman-b')->assertNotFound();
    $this->get('/slug-tidak-ada')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get('/slug-tidak-ada')->assertNotFound();
});

test('{slug}: preorder page publish tampil, draft 404', function () {
    PreorderPage::create(['title' => 'PO A', 'slug' => 'po-a', 'content' => '<p>isi</p>', 'status' => 'publish']);
    PreorderPage::create(['title' => 'PO B', 'slug' => 'po-b', 'content' => '<p>isi</p>', 'status' => 'draft']);

    $this->get('/po-a')->assertOk();
    $this->get('/po-b')->assertNotFound();
});

test('reset-password/{token}: guest melihat form, token palsu ditolak, token valid mengganti password', function () {
    $this->get('/reset-password/abc?email=a@b.c')->assertOk();

    $user = User::factory()->create();
    $this->post('/reset-password', [
        'token' => 'palsu', 'email' => $user->email, 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
    ])->assertSessionHasErrors('email');

    $token = Password::createToken($user);
    $this->post('/reset-password', [
        'token' => $token, 'email' => $user->email, 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
    ])->assertRedirect('/login');

    expect(Hash::check('passwordbaru1', $user->fresh()->password))->toBeTrue();
});

test('reset-password/{token}: user yang sudah login dialihkan', function () {
    $this->actingAs(User::factory()->create())->get('/reset-password/abc')->assertRedirect();
});

test('route param lain: order/invoice tak dikenal 404, non-admin ke /office tidak melihat order', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/orders/NOPE')->assertNotFound();
    $this->actingAs($user)->get('/orders/NOPE/invoice')->assertNotFound();
    $this->actingAs($user)->get('/office/orders/NOPE')->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get('/office/pages/9999/edit')->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get('/office/preorder/9999/edit')->assertNotFound();
});

test('developer: pencarian dan halaman developer mencakup game yang punya >1 developer', function () {
    $devA = Developer::factory()->create(['name' => 'StudioAlfa']);
    $devB = Developer::factory()->create(['name' => 'StudioBeta']);
    $game = Game::factory()->create();
    $game->developers()->attach([$devA->id, $devB->id]);
    $product = Product::factory()->create(['game_id' => $game->id, 'developer_id' => $devA->id, 'is_active' => true]);

    $ids = fn (string $q) => app(ProductSearchService::class)->search($q)->pluck('id')->all();
    expect($ids('StudioAlfa'))->toContain($product->id)
        ->and($ids('StudioBeta'))->toContain($product->id);

    $this->actingAs(User::factory()->create())->get("/developer/{$devB->slug}")->assertOk()->assertSee($product->name);
});

test('ganti email mereset verifikasi dan mengirim link verifikasi ke email baru', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $payload = [
        'name' => $user->name, 'email' => 'baru@example.com', 'no_hp' => $user->no_hp,
        'provinsi' => 'Jawa Barat', 'kota' => 'Bandung', 'kecamatan' => 'Coblong', 'alamat' => 'Jl. Contoh 1', 'kode_pos' => '40132',
    ];

    $this->actingAs($user)->put('/profile/edit', $payload)->assertRedirect();

    expect($user->fresh()->email)->toBe('baru@example.com')
        ->and($user->fresh()->email_verified_at)->toBeNull();
    Notification::assertSentTo($user->fresh(), VerifyEmailNotification::class);
});

test('update profil tanpa ganti email tidak mereset verifikasi', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Nama Baru', 'email' => $user->email, 'no_hp' => $user->no_hp,
        'provinsi' => 'Jawa Barat', 'kota' => 'Bandung', 'kecamatan' => 'Coblong', 'alamat' => 'Jl. Contoh 1', 'kode_pos' => '40132',
    ])->assertRedirect();

    expect($user->fresh()->email_verified_at)->not->toBeNull();
    Notification::assertNothingSent();
});

test('grafik penjualan dan pendapatan dashboard hanya uang bersih (tanpa hangus/refund/gagal)', function () {
    $admin = User::factory()->admin()->create();
    $make = fn (string $status, int $total) => Order::factory()->create(['status' => $status, 'total' => $total]);
    $make('selesai', 100000);
    $make('sedang_diproses', 50000);
    foreach (['hangus', 'hangus_permanen', 'dana_dikembalikan', 'pembayaran_gagal', 'menunggu_pembayaran'] as $status) {
        $make($status, 999999);
    }

    $data = $this->actingAs($admin)->getJson('/office/reports/sales-chart')->assertOk()->json();

    expect(collect($data)->sum('revenue'))->toEqual(150000.0);
});

test('GA4 hanya dirender di layout publik bila Measurement ID valid terisi', function () {
    $this->get('/login')->assertDontSee('googletagmanager.com', false);

    \App\Models\Setting::updateOrCreate(['key' => 'ga4_measurement_id'], ['value' => 'G-ABCD123456']);
    Cache::forget('app_settings_all');

    $this->get('/login')->assertSee('googletagmanager.com/gtag/js?id=G-ABCD123456', false);

    \App\Models\Setting::updateOrCreate(['key' => 'ga4_measurement_id'], ['value' => '"><script>alert(1)</script>']);
    Cache::forget('app_settings_all');
    $this->get('/login')->assertDontSee('alert(1)', false);
});

test('widget Turnstile tampil di form login, register, lupa & reset password bila dikonfigurasi', function () {
    config(['services.turnstile.site_key' => 'site-key-test', 'services.turnstile.secret' => 'secret-test']);

    foreach (['/login', '/register', '/forgot-password', '/reset-password/abc'] as $url) {
        $this->get($url)->assertSee('class="cf-turnstile"', false)->assertSee('site-key-test', false);
    }

    config(['services.turnstile.site_key' => null]);
    $this->get('/login')->assertDontSee('cf-turnstile', false);
});
