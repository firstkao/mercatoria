<?php

use App\Models\Product;
use App\Models\User;

test('footer landing menampilkan produk terpopuler', function () {
    productFinal255()->update(['name' => 'Produk Populer Satu', 'sold_count' => 9]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('Produk Terpopuler')
        ->toContain('Produk Populer Satu')
        ->toContain(format_rp(255000));
});

test('footerTopProducts berstruktur image url name price terurut sold', function () {
    productFinal255()->update(['name' => 'Kurang Laku', 'sold_count' => 1]);
    productFinal85()->update(['name' => 'Paling Laku', 'sold_count' => 50]);

    $footerTopProducts = collect($this->get('/')->assertOk()->viewData('footerTopProducts'));

    expect($footerTopProducts->pluck('name')->all())->toBe(['Paling Laku', 'Kurang Laku'])
        ->and($footerTopProducts->first()['url'])->toContain('/')
        ->and($footerTopProducts->first()['price'])->toBe(85000.0)
        ->and($footerTopProducts->first())->toHaveKeys(['image', 'url', 'name', 'price']);
});

test('produk nonaktif tidak masuk footerTopProducts', function () {
    productFinal255()->update(['name' => 'Aktif Saja', 'sold_count' => 5]);
    Product::factory()->create(['name' => 'Produk Nonaktif', 'is_active' => false, 'sold_count' => 99]);

    $footerTopProducts = collect($this->get('/')->assertOk()->viewData('footerTopProducts'));

    expect($footerTopProducts->pluck('name')->all())->toBe(['Aktif Saja']);
});

test('footerTopProducts juga tampil di halaman layouts app', function () {
    $user = User::factory()->create();
    productFinal255()->update(['name' => 'Produk Footer App', 'sold_count' => 9]);

    $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

    expect($html)->toContain('Produk Footer App');
});
