<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('dashboard adalah tujuan setelah login', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
        ->assertRedirect('/dashboard');
});

test('halaman dashboard tampil sesuai prd 7.16 dan design spec 10.1', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'tier' => 'gold']);
    [$user, $order] = placeTestOrder($user);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard Pelanggan')
        ->assertSee('Selamat datang')
        ->assertSee($user->name)
        ->assertSee('Terdaftar sejak')
        ->assertSee('TOTAL PESANAN')
        ->assertSee('TOTAL BELANJA')
        ->assertSee('KOIN AKTIF')
        ->assertSee('UNDANG TEMAN')
        ->assertSee('TAGIHAN MENUNGGU')
        ->assertSee('DAFTAR PESANAN SAYA')
        ->assertSee('Pesanan Saya')
        ->assertSee('Koin')
        ->assertSee('Undang Teman')
        ->assertSee('Edit Profil')
        ->assertSee($order->no_order)
        ->assertSee('MVP Gold');
});

test('dashboard menampilkan badge role dan tier', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'tier' => 'silver']);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('MVP Silver');
});

test('tagihan menunggu pelunasan menampilkan timer tanpa tombol lunas', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);
    $order->update([
        'status' => 'menunggu_pelunasan_pengambilan',
        'pickup_deadline_at' => now()->addDays(6)->addHours(18),
    ]);

    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    expect($html)->toContain('Menunggu Pelunasan')
        ->toContain('Sisa Waktu')
        ->not->toContain('Lunasi & Ambil');
});

test('profile redirect ke dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')->assertRedirect('/dashboard');
});

test('verify sukses mengarah ke dashboard', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->get($url)->assertRedirect('/dashboard');
});
