<?php

use App\Models\Crush;
use App\Models\Customer;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use Illuminate\Support\Facades\URL;

test('register lalu verifikasi email menjadikan state crush', function () {
    $this->post('/register', registerPayload([
        'email' => 'state-crush@example.com',
        'no_hp' => '081234567051',
    ]))->assertRedirect('/email/verify');

    $user = User::where('email', 'state-crush@example.com')->firstOrFail();
    expect($user->role)->toBe('crush');

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($url)->assertRedirect('/dashboard');

    expect($user->fresh()->role)->toBe('crush')
        ->and(Crush::where('user_id', $user->id)->exists())->toBeTrue()
        ->and(Customer::where('user_id', $user->id)->exists())->toBeFalse();
});

test('order pertama terverifikasi menaikkan state crush ke customer mvp', function () {
    $this->post('/register', registerPayload([
        'email' => 'naik-mvp@example.com',
        'no_hp' => '081234567052',
    ]))->assertRedirect('/email/verify');

    $user = User::where('email', 'naik-mvp@example.com')->firstOrFail();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($url)->assertRedirect('/dashboard');

    [, $order] = placeTestOrder($user);

    // Checkout saja (belum ada pembayaran terverifikasi) belum menaikkan state.
    expect($user->fresh()->role)->toBe('crush')
        ->and(Customer::where('user_id', $user->id)->exists())->toBeFalse();

    $statuses = app(OrderStatusService::class);
    $statuses->changeStatus($order, 'ditahan', 'admin');
    $statuses->changeStatus($order, 'pembayaran_diterima', 'admin');

    expect($user->fresh()->role)->toBe('customer')
        ->and(Customer::where('user_id', $user->id)->value('current_state'))->toBe('MVP')
        ->and(Crush::where('user_id', $user->id)->exists())->toBeFalse();
});
