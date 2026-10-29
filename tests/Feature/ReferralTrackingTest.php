<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\VisitorTracking;
use Illuminate\Support\Facades\Cookie;

test('klik link referral memasang cookie atribusi', function () {
    $referrer = User::factory()->create();

    $this->get('/?ref='.$referrer->referral_code)->assertOk();

    $cookie = Cookie::queued('referral_attribution');
    expect($cookie)->not->toBeNull();

    $payload = json_decode($cookie->getValue(), true);
    expect($payload['code'])->toBe($referrer->referral_code)
        ->and($payload['set_at'])->not->toBeNull();
});

test('klik link referral lalu daftar dalam 30 hari referral tercatat', function () {
    $referrer = User::factory()->create();

    // Klik link share/referral → cookie atribusi (30 hari)
    $this->get('/?ref='.$referrer->referral_code)->assertOk();

    // Browser membawa cookie saat daftar (tanpa isi kolom referral_code)
    $this->withCookie('referral_attribution', json_encode([
        'code' => $referrer->referral_code,
        'set_at' => now()->toIso8601String(),
    ]))->post('/register', registerPayload([
        'email' => 'dalam30@example.com',
        'no_hp' => '081234567001',
    ]))->assertRedirect();

    $user = User::where('email', 'dalam30@example.com')->firstOrFail();
    expect($user->referred_by)->toBe($referrer->id);
});

test('atribusi cookie lewat 30 hari tidak dipakai', function () {
    $referrer = User::factory()->create();

    $this->withCookie('referral_attribution', json_encode([
        'code' => $referrer->referral_code,
        'set_at' => now()->subDays(31)->toIso8601String(),
    ]))->post('/register', registerPayload([
        'email' => 'lewat30@example.com',
        'no_hp' => '081234567002',
    ]))->assertRedirect();

    $user = User::where('email', 'lewat30@example.com')->firstOrFail();
    expect($user->referred_by)->toBeNull();
});

test('kunjungan pertama ditrack ke visitor_tracking dengan landing_page', function () {
    $this->get('/?utm_source=twitter&utm_medium=social')->assertOk();

    $row = VisitorTracking::sole();

    expect($row->landing_page)->toContain('utm_source=twitter')
        ->toContain('utm_medium=social')
        ->and($row->source)->toBe('twitter')
        ->and($row->medium)->toBe('social')
        ->and($row->user_id)->toBeNull();
});

test('kunjungan kedua tidak ditrack dobel', function () {
    $this->get('/')->assertOk();
    expect(VisitorTracking::count())->toBe(1);

    $this->withCookie('visitor_tracked', '1')->get('/faq')->assertOk();
    expect(VisitorTracking::count())->toBe(1);
});

test('user login tidak dobel-tracked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertOk();
    $this->actingAs($user)->withCookie('visitor_tracked', '1')->get('/cara-belanja')->assertOk();

    expect(VisitorTracking::count())->toBe(1)
        ->and(VisitorTracking::sole()->user_id)->toBe($user->id);
});

test('halaman login register cart checkout tidak ditrack', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
    $this->actingAs($user)->get('/cart')->assertOk();
    $this->actingAs($user)->get('/checkout?items[]='.$cart->id)->assertOk();
    $this->actingAs($user)->get('/dashboard')->assertOk();

    expect(VisitorTracking::count())->toBe(0);
});

test('kode referral dari form menang atas cookie atribusi', function () {
    $refCookie = User::factory()->create();
    $refForm = User::factory()->create();

    $this->withCookie('referral_attribution', json_encode([
        'code' => $refCookie->referral_code,
        'set_at' => now()->toIso8601String(),
    ]))->post('/register', registerPayload([
        'email' => 'form-menang@example.com',
        'no_hp' => '081234567003',
        'referral_code' => $refForm->referral_code,
    ]))->assertRedirect();

    $user = User::where('email', 'form-menang@example.com')->firstOrFail();
    expect($user->referred_by)->toBe($refForm->id);
});

test('atribusi cookie tidak berlaku untuk self-referral', function () {
    $user = User::factory()->create(['no_hp' => '6281234567004']);

    $this->withCookie('referral_attribution', json_encode([
        'code' => $user->referral_code,
        'set_at' => now()->toIso8601String(),
    ]))->post('/register', registerPayload([
        'email' => 'self@example.com',
        'no_hp' => '081234567004',
    ]))->assertRedirect();

    $baru = User::where('email', 'self@example.com')->firstOrFail();
    expect($baru->referred_by)->toBeNull();
});
