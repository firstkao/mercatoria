<?php

use App\Models\User;
use App\Models\UserNotification;
use App\Services\Order\OrderStatusService;
use App\Services\User\UserNotificationService;

test('event koin menghasilkan notifikasi ke profile koin', function () {
    $user = User::factory()->create();

    app(UserNotificationService::class)->notify($user, 'coin_earned', 'Koin bertambah', '/profile/koin');

    $n = UserNotification::where('user_id', $user->id)->firstOrFail();
    expect($n->type)->toBe('coin_earned')
        ->and($n->title)->toBe('Koin bertambah')
        ->and($n->target_url)->toBe('/profile/koin')
        ->and($n->is_read)->toBeFalse();
});

test('kredit koin dari order selesai membuat notifikasi', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    advanceToSelesai($order);

    expect(UserNotification::where('user_id', $user->id)->where('type', 'coin_earned')->exists())->toBeTrue();
});

test('status order berubah membuat notifikasi ke detail order', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    app(OrderStatusService::class)->changeStatus($order, 'ditahan', 'user');

    $n = UserNotification::where('user_id', $user->id)->where('type', 'order_status')->firstOrFail();
    expect($n->target_url)->toContain('/orders/');
});

test('notif hanya untuk user pemilik', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    app(UserNotificationService::class)->notify($user, 'info', 'Untukmu', '/profile');

    expect(UserNotification::where('user_id', $other->id)->count())->toBe(0);
});
