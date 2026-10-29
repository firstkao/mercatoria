<?php

use App\Models\ActivityLog;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('register dan login mencatat activity log', function () {
    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->post('/register', registerPayload(['email' => 'log1@example.com', 'no_hp' => '081234567021']))
        ->assertRedirect();

    $reg = ActivityLog::where('action', 'register')->sole();
    expect($reg->user_id)->toBe(User::where('email', 'log1@example.com')->firstOrFail()->id)
        ->and($reg->url)->toContain('/register')
        ->and($reg->ip_address)->toBe('203.0.113.5');

    auth()->logout();

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->post('/login', ['email' => 'log1@example.com', 'password' => 'password123'])->assertRedirect();

    expect(ActivityLog::where('action', 'login')->count())->toBe(1);
});

test('checkout dan upload bukti mencatat activity log', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => productFinal255()->id]);

    $this->actingAs($user)->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertRedirect();

    $orderLog = ActivityLog::where('action', 'order_created')->sole();
    expect($orderLog->user_id)->toBe($user->id);

    $order = $user->orders()->latest('id')->firstOrFail();
    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    $payLog = ActivityLog::where('action', 'upload_payment_proof')->sole();
    expect($payLog->user_id)->toBe($user->id)
        ->and($payLog->metadata['no_order'] ?? null)->toBe($order->no_order);
});

test('edit profil dan ubah password mencatat activity log dengan metadata', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Nama Baru Log',
        'email' => $user->email,
        'no_hp' => $user->no_hp,
        'provinsi' => 'Bali',
        'kota' => 'Denpasar',
        'kecamatan' => 'Denpasar',
        'alamat' => 'Jl. Log 1',
        'kode_pos' => '80111',
    ])->assertRedirect();

    $edit = ActivityLog::where('action', 'edit_profile')->sole();
    expect($edit->user_id)->toBe($user->id)
        ->and($edit->metadata)->toBeArray();

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'password',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertRedirect();

    expect(ActivityLog::where('action', 'change_password')->count())->toBe(1);
});
