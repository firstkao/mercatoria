<?php

use App\Models\User;
use App\Models\UserNotification;

test('endpoint unread count memberi jumlah notifikasi belum dibaca', function () {
    $user = User::factory()->create();
    UserNotification::factory()->count(2)->create(['user_id' => $user->id, 'is_read' => false]);
    UserNotification::factory()->create(['user_id' => $user->id, 'is_read' => true]);

    $this->actingAs($user)->getJson('/profile/notifications/unread-count')
        ->assertOk()
        ->assertExactJson(['count' => 2]);
});

test('guest ditolak mengakses unread count', function () {
    $this->getJson('/profile/notifications/unread-count')->assertStatus(401);
});
