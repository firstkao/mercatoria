<?php

use App\Models\User;

test('guest diarahkan ke login untuk dashboard user', function () {
    $this->get('/profile')->assertRedirect('/login');
});

test('user melihat dashboard dengan 4 tab dan info bar', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('Pesanan Saya')
        ->assertSee('Koin Saya')
        ->assertSee('Undang Teman')
        ->assertSee('Edit Profil')
        ->assertSee($order->no_order);
});

test('dashboard menampilkan tagihan menunggu', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user); // status menunggu_pembayaran

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('TAGIHAN MENUNGGU')
        ->assertSee($order->no_order);
});

test('navbar memuat link cart dan dashboard', function () {
    $user = User::factory()->create();

    // Halaman ber-layout app (welcome/landing masih skeleton fase j)
    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('href="'.route('cart.index').'"', false)
        ->assertSee('href="'.route('profile.show').'"', false);
});
