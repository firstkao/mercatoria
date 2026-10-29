<?php

use App\Models\Cart;
use App\Models\CartReminder;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\CartReminderNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Notification;

test('command mengirim reminder bell dan email ke user dengan item cart', function () {
    Notification::fake();
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->artisan('cart:send-reminder')->assertSuccessful();

    expect(UserNotification::where('user_id', $user->id)->where('type', 'cart_reminder')->exists())->toBeTrue()
        ->and(CartReminder::where('user_id', $user->id)->exists())->toBeTrue();

    Notification::assertSentTo($user, CartReminderNotification::class);
});

test('user tanpa item cart tidak dapat reminder', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->artisan('cart:send-reminder')->assertSuccessful();

    expect(CartReminder::count())->toBe(0);
    Notification::assertNotSentTo($user, CartReminderNotification::class);
});

test('item yang sudah dihapus dari cart tidak dapat reminder', function () {
    Notification::fake();
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);
    $cart->delete();

    $this->artisan('cart:send-reminder')->assertSuccessful();

    Notification::assertNotSentTo($user, CartReminderNotification::class);
});

test('reminder tidak dikirim dua kali dalam 30 menit', function () {
    Notification::fake();
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->artisan('cart:send-reminder')->assertSuccessful();
    $this->artisan('cart:send-reminder')->assertSuccessful();

    expect(CartReminder::where('user_id', $user->id)->count())->toBe(1);
    Notification::assertSentToTimes($user, CartReminderNotification::class, 1);
});

test('reminder kembali terkirim setelah 30 menit', function () {
    Notification::fake();
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->artisan('cart:send-reminder')->assertSuccessful();
    CartReminder::query()->update(['created_at' => now()->subMinutes(31)]);
    $this->artisan('cart:send-reminder')->assertSuccessful();

    expect(CartReminder::where('user_id', $user->id)->count())->toBe(2);
});

test('jadwal cart:send-reminder terdaftar tiap 30 menit', function () {
    $events = collect(app(Schedule::class)->events());
    $event = $events->first(fn ($e) => str_contains((string) $e->command, 'cart:send-reminder'));

    expect($event->expression)->toBe('*/30 * * * *')
        ->and($event->timezone)->toBe('Asia/Jakarta');
});
