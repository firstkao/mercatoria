<?php

use App\Models\User;

test('user baru belum lihat tour punya flag false dan shouldSeeTour true', function () {
    $user = User::factory()->create();

    expect($user->has_seen_tour)->toBeFalse()
        ->and($user->shouldSeeTour())->toBeTrue();
});

test('endpoint menandai tour sudah dilihat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/tour-seen')->assertRedirect();

    expect($user->fresh()->has_seen_tour)->toBeTrue()
        ->and($user->fresh()->shouldSeeTour())->toBeFalse();
});

test('guest tidak bisa menandai tour', function () {
    $this->post('/profile/tour-seen')->assertRedirect('/login');
});

test('user belum verifikasi email tidak memicu tour', function () {
    $user = User::factory()->unverified()->create();

    expect($user->shouldSeeTour())->toBeFalse();
});

test('flag has_seen_tour persisten antar sesi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/tour-seen');
    auth()->logout();

    expect(User::find($user->id)->has_seen_tour)->toBeTrue();
});
