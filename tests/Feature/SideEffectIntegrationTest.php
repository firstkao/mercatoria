<?php

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\SplitBoxBatchSlot;
use App\Models\User;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\UploadedFile;

/**
 * Integration test: transisi status penuh lewat OrderStatusService harus
 * memicu efek nyata (koin, slot, tier, slot_sold) — bukan hanya unit service.
 */
test('perjalanan penuh — lock, kredit koin, tier, selesai', function () {
    $referrer = User::factory()->create();
    Coin::factory()->create(['user_id' => $referrer->id, 'balance' => 0]);

    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Coin::factory()->create(['user_id' => $referee->id, 'balance' => 12750]);

    $fx = splitBoxFixture();
    [$user, $order] = placeSplitBoxOrder($referee, $fx['product'], [$fx['hot'], $fx['normalA']], 1, [
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]);

    // 1) Upload bukti → slot terkunci nyata + koin terpotong
    $this->actingAs($referee)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $order->refresh();
    expect($order->status)->toBe('ditahan')
        ->and((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(1)
        ->and((int) SplitBoxBatchSlot::where('slot_id', $fx['normalA']->id)->value('qty_taken'))->toBe(1)
        ->and(Coin::where('user_id', $referee->id)->value('balance'))->toBe(0)
        ->and(CoinTransaction::where('order_id', $order->id)->where('type', 'used_at_checkout')->exists())->toBeTrue();

    // 2) Verifikasi → referee dapat 1.000 (pembayaran pertama terverifikasi)
    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $this->actingAs(User::factory()->admin()->create())
        ->post("/office/orders/{$order->no_order}/payments/{$payment->id}/verify")->assertRedirect();

    expect(Coin::where('user_id', $referee->id)->value('balance'))->toBe(1000)
        ->and($order->fresh()->status)->toBe('pembayaran_diterima');

    // 3) Sampai Selesai → referrer 3.000 + pembeli 1% (dari orders.total 417.250) + tier terhitung
    $statuses = app(OrderStatusService::class);
    foreach (['sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'sedang_dikemas', 'selesai'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }

    // subtotal 430.000 (2 slot × 215.000) − koin 12.750 = total 417.250 → 1% = 4.172
    expect(Coin::where('user_id', $referrer->id)->value('balance'))->toBe(3000)
        ->and(Coin::where('user_id', $referee->id)->value('balance'))->toBe(1000 + 4172)
        ->and((float) Customer::where('user_id', $referee->id)->value('total_belanja'))->toEqual(417250.0);
});

test('slot_sold nyata — order lain batal, koin hangus, voucher hangus', function () {
    $fx = splitBoxFixture(); // stok 1 per slot

    // Order A mengunci stok lewat upload
    [$userA, $orderA] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    $this->actingAs($userA)->post("/orders/{$orderA->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-a.jpg'),
    ]);
    $orderA->refresh();
    expect($orderA->status)->toBe('ditahan');

    // Order B (dengan koin + voucher tidak bisa bareng; pakai koin) memuat slot sama
    $userB = User::factory()->create();
    Coin::factory()->create(['user_id' => $userB->id, 'balance' => 12750]);
    [$userB, $orderB] = placeSplitBoxOrder($userB, $fx['product'], [$fx['hot'], $fx['normalA']], 1, [
        'discount_type' => 'coin',
        'coin_amount' => 12750,
    ]);

    // Upload B → lock gagal → slot_sold + forfeit
    $this->actingAs($userB)->post("/orders/{$orderB->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-b.jpg'),
    ]);

    $orderB->refresh();
    expect($orderB->status)->toBe('pembayaran_gagal')
        ->and($orderB->cancel_reason)->toBe('slot_sold')
        ->and(CoinTransaction::where('order_id', $orderB->id)->where('type', 'forfeit_at_checkout')->exists())->toBeTrue()
        ->and(Coin::where('user_id', $userB->id)->value('balance'))->toBe(0)
        // slot A tetap terkunci (all-or-nothing B tidak mengubah stok)
        ->and((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(1);
});

test('reject melepas slot nyata — upload ulang mengunci lagi', function () {
    $fx = splitBoxFixture();
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);
    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(1);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/reject");

    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(0);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-2.jpg'),
    ]);
    expect((int) SplitBoxBatchSlot::where('slot_id', $fx['hot']->id)->value('qty_taken'))->toBe(1);
});

test('refund setelah selesai memotong tier kembali', function () {
    $user = User::factory()->create();
    [$user, $order] = placeTestOrder($user);

    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses'] as $s) {
        $statuses->changeStatus($order, $s, 'admin');
    }
    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(255000.0);

    $statuses->changeStatus($order, 'dana_dikembalikan', 'admin');

    expect((float) Customer::where('user_id', $user->id)->value('total_belanja'))->toEqual(0.0);
});
