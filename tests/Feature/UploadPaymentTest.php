<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('upload bukti bayar sukses tersimpan di folder privat', function () {
    [$user, $order] = placeTestOrder();

    Storage::fake('local');

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect($payment->status)->toBe('pending')
        ->and($payment->type)->toBe('full')
        ->and($payment->file_path)->toContain('payment-proofs');

    Storage::disk('local')->assertExists($payment->file_path);
    expect($order->fresh()->status)->toBe('ditahan');
});

test('file nama sesuai format prd 17.9', function () {
    [$user, $order] = placeTestOrder();

    Storage::fake('local');

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect(basename($payment->file_path))
        ->toStartWith("{$order->no_order} - full - bca");
});

test('upload ditolak bila tipe file bukan jpg jpeg png', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0)
        ->and($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('upload ditolak bila ukuran lebih dari 1 mb', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->create('bukti.jpg', 2048, 'image/jpeg'),
    ])->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0)
        ->and($order->fresh()->status)->toBe('menunggu_pembayaran');
});

test('upload ditolak bila tanpa file', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [])
        ->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0);
});

test('form upload terkunci setelah submit satu kali', function () {
    [$user, $order] = placeTestOrder();

    Storage::fake('local');

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertRedirect();

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-lagi.jpg'),
    ])->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(1);
});

test('setelah ditolak boleh upload ulang', function () {
    $admin = User::factory()->admin()->create();
    [$user, $order] = placeTestOrder();

    Storage::fake('local');

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ]);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    $this->actingAs($admin)->post("/office/orders/{$order->no_order}/payments/{$payment->id}/reject", ['reason' => 'Bukti tidak jelas']);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti-2.jpg'),
    ])->assertRedirect();

    expect(Payment::count())->toBe(2)
        ->and($order->fresh()->status)->toBe('ditahan');
});

test('upload ditolak bila status order bukan menunggu pembayaran', function () {
    [$user, $order] = placeTestOrder();

    $order->update(['status' => 'sedang_diproses']);

    $this->actingAs($user)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0);
});
