<?php

use App\Models\ProductView;
use App\Models\User;

test('product view tercatat unik per produk untuk user crush', function () {
    $user = User::factory()->create(['role' => User::ROLE_CRUSH]);
    $product = productFinal255();

    $this->actingAs($user)->get('/'.$product->game->slug.'/'.$product->slug)->assertOk();
    $this->actingAs($user)->get('/'.$product->game->slug.'/'.$product->slug)->assertOk();

    expect(ProductView::where('user_id', $user->id)->where('product_id', $product->id)->count())->toBe(1);

    // MVP unlimited: view tidak dicatat.
    $mvp = User::factory()->create();
    $this->actingAs($mvp)->get('/'.$product->game->slug.'/'.$product->slug)->assertOk();

    expect(ProductView::where('user_id', $mvp->id)->exists())->toBeFalse();
});

test('kuota view berkurang per produk unik dan tampil di dashboard', function () {
    $user = User::factory()->create(['role' => User::ROLE_CRUSH]);
    $pertama = productFinal255();
    $kedua = productFinal255();

    $this->actingAs($user)->get('/'.$pertama->game->slug.'/'.$pertama->slug)->assertOk();
    $this->actingAs($user)->get('/'.$kedua->game->slug.'/'.$kedua->slug)->assertOk();
    $this->actingAs($user)->get('/'.$pertama->game->slug.'/'.$pertama->slug)->assertOk();

    $dashboard = $this->actingAs($user)->get('/dashboard')->assertOk();

    expect($dashboard->viewData('userState'))->toBe('crush')
        ->and($dashboard->viewData('kuotaViewUsed'))->toBe(2)
        ->and($dashboard->viewData('kuotaViewTotal'))->toBe(10);

    // MVP: blok kuota/countdown disembunyikan.
    $mvp = User::factory()->create();
    $dashboardMvp = $this->actingAs($mvp)->get('/dashboard')->assertOk();

    expect($dashboardMvp->viewData('userState'))->toBe('mvp')
        ->and($dashboardMvp->viewData('kuotaViewUsed'))->toBeNull()
        ->and($dashboardMvp->viewData('countdownState'))->toBeNull();
});

test('countdown state format sisa waktu hari jam', function () {
    $this->travelTo(now()->startOfDay()->addHours(12));
    $user = User::factory()->create([
        'role' => User::ROLE_CRUSH,
        'email_verified_at' => now()->subDays(5)->subHours(18),
    ]);

    $dashboard = $this->actingAs($user)->get('/dashboard')->assertOk();

    expect($dashboard->viewData('countdownState'))->toBe('Sisa waktu: 24 Hari 6 Jam');

    // Belum verifikasi: countdown tidak dihitung (PRD 7.10 dihitung sejak verifikasi).
    $belum = User::factory()->unverified()->create(['role' => User::ROLE_CRUSH]);
    $dashboardBelum = $this->actingAs($belum)->get('/dashboard')->assertOk();

    expect($dashboardBelum->viewData('countdownState'))->toBe('Belum verifikasi');
});
