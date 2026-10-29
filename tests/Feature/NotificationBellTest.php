<?php

use App\Models\User;
use App\Models\UserNotification;

test('bell menampilkan badge jumlah belum dibaca', function () {
    $user = User::factory()->create();
    UserNotification::factory()->count(3)->create(['user_id' => $user->id, 'is_read' => false]);

    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    expect($html)->toContain('notification-bell')
        ->toContain('3');
});

test('badge tersembunyi saat tidak ada notifikasi', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    expect($html)->not->toContain('data-unread="0"')
        ->toContain('notification-bell');
});

test('dropdown menampilkan 10 notifikasi terbaru', function () {
    $user = User::factory()->create();
    UserNotification::factory()->count(12)->create(['user_id' => $user->id, 'is_read' => false]);

    // /cart hanya punya bell navbar (dashboard punya 2 bell: navbar + header)
    $html = $this->actingAs($user)->get('/cart')->getContent();

    $count = substr_count($html, 'data-notification-item');
    expect($count)->toBe(10);
});

test('tombol tandai semua dibaca mengubah status', function () {
    $user = User::factory()->create();
    UserNotification::factory()->count(2)->create(['user_id' => $user->id, 'is_read' => false]);

    $this->actingAs($user)->post('/profile/notifications/read-all')->assertRedirect();

    expect(UserNotification::where('user_id', $user->id)->where('is_read', false)->count())->toBe(0);
});

test('halaman semua notifikasi tampil dengan pagination', function () {
    $user = User::factory()->create();
    UserNotification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Koin bertambah',
        'target_url' => '/profile/koin',
    ]);

    $this->actingAs($user)->get('/profile/notifications')
        ->assertOk()
        ->assertSee('Koin bertambah')
        ->assertSee('/profile/koin', false);
});

test('notif expired 90 hari dibersihkan oleh notification purge', function () {
    $user = User::factory()->create();
    UserNotification::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(91)]);
    UserNotification::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDay()]);

    $this->artisan('notification:purge')->assertSuccessful();

    expect(UserNotification::count())->toBe(1);
});
