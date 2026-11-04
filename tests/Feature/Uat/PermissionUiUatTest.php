<?php

use App\Models\User;
use Illuminate\Routing\Route;

/**
 * @return array<int, Route>
 */
function uatOfficeRoutes(): array
{
    return collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn (Route $r) => str_starts_with($r->uri(), 'office'))
        ->values()->all();
}

test('UAT permission: customer ditolak (403) di SEMUA route /office, semua method', function () {
    $this->actingAs(User::factory()->create());

    foreach (uatOfficeRoutes() as $route) {
        $uri = preg_replace('/\{[^}]+\}/', '1', $route->uri());
        $method = collect($route->methods())->first(fn ($m) => ! in_array($m, ['HEAD'], true));

        $status = $this->call($method, '/'.$uri)->status();
        // Route dengan parameter yang tidak ditemukan dijawab 404 oleh route-binding, tanpa membocorkan data.
        expect($status)->toBeIn(str_contains($route->uri(), '{') ? [403, 404] : [403], "{$method} /{$uri} customer");
    }
});

test('UAT permission: guest dialihkan ke login di SEMUA route /office', function () {
    foreach (uatOfficeRoutes() as $route) {
        $uri = preg_replace('/\{[^}]+\}/', '1', $route->uri());
        $method = collect($route->methods())->first(fn ($m) => $m !== 'HEAD');

        $response = $this->call($method, '/'.$uri);
        // Route bukti bayar menjawab 403 untuk guest/non-admin (middleware admin tanpa auth).
        expect($response->status())->toBeIn(
            str_contains($route->uri(), 'payment-proofs') ? [302, 401, 419, 403] : [302, 401, 419],
            "{$method} /{$uri}"
        );
    }
});

test('UAT permission: customer tidak melihat sidebar admin pada halamannya', function () {
    $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
        ->assertDontSee('id="sidebar-nav"', false)
        ->assertDontSee('/office/stores', false);
});

test('UAT UI integrity: semua halaman GET admin tanpa parameter berstatus 200', function () {
    $this->actingAs(User::factory()->admin()->create());

    foreach (uatOfficeRoutes() as $route) {
        if (! in_array('GET', $route->methods(), true) || str_contains($route->uri(), '{')) {
            continue;
        }

        $this->get('/'.$route->uri())->assertOk("GET /{$route->uri()} tidak 200");
    }
});

test('UAT UI integrity: halaman publik tidak menghasilkan 500', function (string $uri) {
    expect($this->get($uri)->status())->toBeLessThan(500);
})->with(['/', '/tentang-kami', '/faq', '/tnc', '/cara-belanja', '/kebijakan-privasi', '/preorder-baru', '/menyusul', '/halaman-tidak-ada']);

test('UAT UI integrity: 404 untuk slug tidak dikenal, bukan 500', function () {
    $this->get('/slug-yang-tidak-ada')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get('/slug-yang-tidak-ada')->assertNotFound();
    $this->get('/game-x/produk-y')->assertNotFound();
});

test('UAT UI integrity: halaman customer terautentikasi tidak error', function (string $uri) {
    $this->actingAs(User::factory()->create())->get($uri)->assertOk();
})->with(['/dashboard', '/cart', '/orders', '/profile/edit', '/profile/koin', '/profile/wallet', '/profile/notifications']);

test('UAT UI integrity: flash message tampil di layout admin setelah aksi', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->followingRedirects()
        ->post('/office/tags', ['name' => 'Flash Tag'])
        ->assertOk()->assertSee('Tag dibuat.');
});

test('UAT pencarian: global admin dan pencarian publik tidak crash', function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->get('/office/search?q=abc')->assertOk();
    $this->get('/office/search')->assertOk();
    $this->get('/search?q=%3Cscript%3E')->assertOk()->assertDontSee('<script>alert', false);
});
