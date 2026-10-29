<?php

use App\Models\AdminLog;
use App\Models\User;
use App\Models\UserNotification;

test('admin kirim notifikasi manual ke user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/office/users/{$user->id}/notify", [
        'title' => 'Info dari Admin',
        'message' => 'Pesananmu sudah kami cek.',
        'link' => '/orders',
    ])->assertRedirect();

    $notif = UserNotification::where('user_id', $user->id)->where('type', 'admin_manual')->sole();
    expect($notif->title)->toBe('Info dari Admin')
        ->and($notif->message)->toBe('Pesananmu sudah kami cek.')
        ->and($notif->target_url)->toBe('/orders');

    $log = AdminLog::where('model', 'User')->where('action', 'notify')->sole();
    expect($log->model_id)->toBe($user->id)
        ->and($log->new_value['title'] ?? null)->toBe('Info dari Admin');
});

test('notifikasi manual wajib judul dan pesan, non-admin ditolak', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/office/users/{$user->id}/notify", ['title' => ''])
        ->assertSessionHasErrors(['title', 'message']);

    $customer = User::factory()->create();
    $this->actingAs($customer)->post("/office/users/{$user->id}/notify", [
        'title' => 'X',
        'message' => 'Y',
    ])->assertForbidden();

    expect(UserNotification::count())->toBe(0);
});
