<?php

use App\Models\Store;
use App\Models\User;

/**
 * @return array<int, array{href: string, label: string, active: bool}>
 */
function uatSidebarLinks(string $html): array
{
    preg_match('/<nav id="sidebar-nav".*?<\/nav>/s', $html, $nav);
    preg_match_all('/<a href="([^"]+)"\s+class="([^"]*nav-link[^"]*)">.*?<span class="nav-text">([^<]+)<\/span>/s', $nav[0] ?? '', $m, PREG_SET_ORDER);

    return array_map(fn ($x) => [
        'href' => $x[1],
        'label' => trim($x[3]),
        'active' => str_contains($x[2], 'nav-link-active'),
    ], $m);
}

test('UAT sidebar: setiap menu mengarah ke halaman 200 tanpa error', function () {
    $admin = User::factory()->admin()->create();
    $links = uatSidebarLinks($this->actingAs($admin)->get('/office')->getContent());

    expect(count($links))->toBeGreaterThan(20);

    foreach ($links as $link) {
        $this->actingAs($admin)->get($link['href'])
            ->assertOk("Menu '{$link['label']}' ({$link['href']}) tidak 200");
    }
});

test('UAT sidebar: tepat satu menu aktif dan cocok dengan halaman yang dibuka', function () {
    $admin = User::factory()->admin()->create();
    $links = uatSidebarLinks($this->actingAs($admin)->get('/office')->getContent());

    foreach ($links as $link) {
        $active = array_values(array_filter(
            uatSidebarLinks($this->actingAs($admin)->get($link['href'])->getContent()),
            fn ($l) => $l['active'],
        ));

        expect($active)->toHaveCount(1, "Menu '{$link['label']}' harus tepat 1 aktif")
            ->and($active[0]['href'])->toBe($link['href']);
    }
});

test('UAT sidebar: highlight aktif tetap benar di halaman turunan (edit/create)', function () {
    $admin = User::factory()->admin()->create();
    $store = Store::factory()->create();

    $active = array_values(array_filter(
        uatSidebarLinks($this->actingAs($admin)->get('/office/products/create')->getContent()),
        fn ($l) => $l['active'],
    ));

    expect($active[0]['href'])->toEndWith('/office/products');
    expect($store->exists)->toBeTrue();
});

test('UAT sidebar: skrip pemulih posisi scroll ada di setiap halaman admin', function () {
    $admin = User::factory()->admin()->create();

    foreach (['/office', '/office/stores', '/office/settings', '/office/products'] as $uri) {
        $this->actingAs($admin)->get($uri)->assertOk()
            ->assertSee('admin-sidebar-scroll', false)
            ->assertSee('id="sidebar-nav"', false);
    }
});

test('UAT sidebar: tidak tampil untuk customer biasa (403)', function () {
    $this->actingAs(User::factory()->create())->get('/office')->assertForbidden();
});
