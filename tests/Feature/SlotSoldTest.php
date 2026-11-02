<?php

use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Cart\CheckoutService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

test('titik a â€” lock gagal: order batal slot_sold, koin dan voucher dikembalikan', function () {
    $fx = splitBoxFixture();
    $user = $fx['user'];
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 12750]);
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'min_belanja' => 0, 'is_active' => true]);
    $userVoucher = UserVoucher::factory()->create(['user_id' => $user->id, 'voucher_id' => $voucher->id, 'status' => 'active']);

    // Order pakai koin
    [, $order] = placeSplitBoxOrder($user, $fx['product'], [$fx['hot'], $fx['normalA']], 1, [
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]);

    // Stok habis diambil orang lain (lock langsung lewat service)
    $otherUser = User::factory()->create();
    [, $otherOrder] = placeSplitBoxOrder($otherUser, $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($otherOrder);

    // Upload oleh pemilik order pertama â†’ lock gagal â†’ slot_sold
    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $order->refresh();
    expect($order->status)->toBe('pembayaran_gagal')
        ->and($order->cancel_reason)->toBe('slot_sold');

    expect(CoinTransaction::where('order_id', $order->id)->where('type', 'refund_returned')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $user->id)->value('balance'))->toBe(12750);
});

test('titik b â€” stok habis: order lain menunggu pembayaran ikut batal slot_sold', function () {
    $fx = splitBoxFixture();

    // Order A (akan lock) & Order B (menunggu pembayaran, memuat slot sama)
    [$userA, $orderA] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    $userB = User::factory()->create();
    Coin::factory()->create(['user_id' => $userB->id, 'balance' => 12750]);

    // Order B: bundle Hot + Normal A juga (stok cukup untuk 1 paket saja) + koin
    [$userB, $orderB] = placeSplitBoxOrder($userB, $fx['product'], [$fx['hot'], $fx['normalA']], 1, [
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]);

    // A upload â†’ lock sukses â†’ stok habis â†’ B batal otomatis
    $this->actingAs($userA)->post("/orders/{$orderA->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-a.jpg'),
    ]);

    $orderB->refresh();
    expect($orderA->fresh()->status)->toBe('ditahan')
        ->and($orderB->status)->toBe('pembayaran_gagal')
        ->and($orderB->cancel_reason)->toBe('slot_sold')
        ->and(CoinTransaction::where('order_id', $orderB->id)->where('type', 'refund_returned')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $userB->id)->value('balance'))->toBe(12750);
});

test('order lain yang tidak memuat slot terdampak tidak ikut batal', function () {
    $fx = splitBoxFixture();

    [$userA, $orderA] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    // Order C hanya Normal B (slot berbeda)
    $userC = User::factory()->create();
    [$userC, $orderC] = placeSplitBoxOrder($userC, $fx['product'], [$fx['normalB']]);

    $this->actingAs($userA)->post("/orders/{$orderA->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-a.jpg'),
    ]);

    expect($orderC->fresh()->status)->toBe('menunggu_pembayaran')
        ->and($orderC->fresh()->cancel_reason)->toBeNull();
});

test('voucher kembali aktif saat slot sold', function () {
    $fx = splitBoxFixture();
    $user = $fx['user'];
    $voucher = Voucher::factory()->create(['nominal' => 25000, 'min_belanja' => 0, 'is_active' => true]);
    $userVoucher = UserVoucher::factory()->create(['user_id' => $user->id, 'voucher_id' => $voucher->id, 'status' => 'active']);

    $bundleId = (string) Str::uuid();
    $carts = collect([$fx['hot'], $fx['normalA']])->map(fn ($slot) => Cart::create([
        'user_id' => $user->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $slot->variant_id,
        'qty' => 1,
        'bundle_group_id' => $bundleId,
    ]));

    $order = app(CheckoutService::class)->placeOrder($user, $carts->pluck('id')->all(), [
        'platform' => 'toco', 'scheme' => 'fp', 'payment_method' => 'bca',
        'discount_type' => 'voucher', 'user_voucher_id' => $userVoucher->id, 'customer_note' => null,
    ]);

    // Stok diambil orang lain
    $otherUser = User::factory()->create();
    [, $otherOrder] = placeSplitBoxOrder($otherUser, $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($otherOrder);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    expect($userVoucher->fresh()->status)->toBe('active')
        ->and($order->fresh()->cancel_reason)->toBe('slot_sold');
});
