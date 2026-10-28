<?php

use App\Models\Cart;
use App\Models\Developer;
use App\Models\Game;
use App\Models\User;

/**
 * Audit status semua URL area user — regression test dari audit klik nyata.
 */
test('semua url area user merespons sesuai harapan', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $ok = [
        '/', '/search?q=a',
        '/dashboard', '/orders', '/orders/'.$order->no_order, '/orders/'.$order->no_order.'/invoice',
        '/cart', '/checkout?items[]='.$cart->id,
        '/profile/koin', '/profile/wallet', '/profile/undang', '/profile/edit', '/profile/complete',
        '/menyusul',
        '/tentang-kami', '/faq', '/cara-belanja', '/tnc', '/kebijakan-privasi',
    ];

    foreach ($ok as $url) {
        $this->actingAs($user)->get($url)->assertOk();
    }

    $this->actingAs($user)->get('/profile')->assertRedirect('/dashboard');
});

test('halaman menyusul memberi placeholder jelas', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/menyusul')
        ->assertOk()
        ->assertSee('Menyusul');
});

test('halaman publik cms menampilkan placeholder menyusul', function () {
    foreach (['/tentang-kami', '/faq', '/cara-belanja', '/tnc', '/kebijakan-privasi'] as $url) {
        $this->get($url)->assertOk()->assertSee('Menyusul');
    }
});

test('bell notifikasi menautkan placeholder menyusul', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    expect($html)->toContain(route('coming-soon'));
});

test('navbar game dan developer mengarah ke route katalog asli', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'genshin-impact', 'name' => 'Genshin Impact', 'is_active' => true]);
    $developer = Developer::factory()->create(['slug' => 'mihoyo', 'name' => 'miHoYo', 'is_active' => true]);

    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    expect($html)->toContain(route('catalog.game', $game->slug))
        ->and($html)->toContain(route('catalog.developer', $developer->slug))
        ->and($html)->toContain('Genshin Impact')
        ->and($html)->toContain('miHoYo');
});
