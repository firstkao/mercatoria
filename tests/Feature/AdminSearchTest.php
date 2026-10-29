<?php

use App\Models\User;

test('admin search menemukan produk by nama', function () {
    $admin = User::factory()->admin()->create();
    $product = productFinal255();
    $product->update(['name' => 'Kartu Genshin Eksklusif']);

    $json = $this->actingAs($admin)->getJson('/office/search?q=Genshin')->assertOk()->json();

    $titles = collect($json['products'])->pluck('title');
    expect($titles)->toContain('Kartu Genshin Eksklusif');
    expect(collect($json['products'])->first()['url'])->toContain(route('office.products.edit', $product, false));
});

test('admin search menemukan order by no_order', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $json = $this->actingAs($admin)->getJson('/office/search?q='.$order->no_order)->assertOk()->json();

    $titles = collect($json['orders'])->pluck('title');
    expect($titles)->toContain($order->no_order);
    expect(collect($json['orders'])->first()['url'])->toContain(route('office.orders.show', $order, false));
});

test('admin search menemukan user by nama dan email', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Budi Search', 'email' => 'budi.search@example.com']);

    $byName = $this->actingAs($admin)->getJson('/office/search?q=Budi Search')->assertOk()->json();
    expect(collect($byName['users'])->pluck('title'))->toContain('Budi Search');

    $byEmail = $this->actingAs($admin)->getJson('/office/search?q=budi.search@example.com')->assertOk()->json();
    expect(collect($byEmail['users'])->pluck('meta')->implode(' '))->toContain('budi.search@example.com');
});

test('admin search mengembalikan hasil terkelompok per tipe', function () {
    $admin = User::factory()->admin()->create();

    $json = $this->actingAs($admin)->getJson('/office/search?q=zzz-tidak-ada')->assertOk()->json();

    expect(array_keys($json))->toBe(['products', 'orders', 'users'])
        ->and($json['products'])->toBe([])
        ->and($json['orders'])->toBe([])
        ->and($json['users'])->toBe([]);
});

test('non-admin ditolak 403 search admin', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->getJson('/office/search?q=x')->assertForbidden();
});
