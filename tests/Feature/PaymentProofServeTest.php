<?php

use App\Models\Payment;
use App\Models\User;

test('admin dapat membuka bukti bayar via route office', function () {
    $admin = User::factory()->admin()->create();
    @mkdir(storage_path('app/private/payment-proofs'), 0755, true);
    $name = 'MER-TESTA-0001 - full - bca.jpg';
    file_put_contents(storage_path('app/private/payment-proofs/'.$name), 'PROOFDATA');

    $this->actingAs($admin)
        ->get('/office/payment-proofs/'.$name)
        ->assertOk()
        ->assertSee('PROOFDATA', false);

    @unlink(storage_path('app/private/payment-proofs/'.$name));
});

test('guest dan user non-admin ditolak 403', function () {
    @mkdir(storage_path('app/private/payment-proofs'), 0755, true);
    $name = 'MER-TESTB-0001 - full - bca.jpg';
    file_put_contents(storage_path('app/private/payment-proofs/'.$name), 'PROOFDATA');

    $this->get('/office/payment-proofs/'.$name)->assertForbidden();

    $user = User::factory()->create();
    $this->actingAs($user)->get('/office/payment-proofs/'.$name)->assertForbidden();

    @unlink(storage_path('app/private/payment-proofs/'.$name));
});

test('path traversal ditolak', function () {
    $admin = User::factory()->admin()->create();
    @mkdir(storage_path('app/private/payment-proofs'), 0755, true);
    file_put_contents(storage_path('app/private/payment-proofs/rahasia.jpg'), 'X');

    $this->actingAs($admin)
        ->get('/office/payment-proofs/'.rawurlencode('../products/rahasia.jpg'))
        ->assertNotFound();

    @unlink(storage_path('app/private/payment-proofs/rahasia.jpg'));
});

test('link Lihat di halaman admin order mengarah ke route bukti bayar dan tampil', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    @mkdir(storage_path('app/private/payment-proofs'), 0755, true);
    $name = 'proof-link-'.uniqid().'.jpg';
    file_put_contents(storage_path('app/private/payment-proofs/'.$name), 'PROOFDATA');
    Payment::factory()->create([
        'order_id' => $order->id,
        'file_path' => 'payment-proofs/'.$name,
    ]);

    $page = $this->actingAs($admin)->get("/office/orders/{$order->no_order}");
    $page->assertOk()->assertSee('/office/payment-proofs/'.$name, false);

    $this->actingAs($admin)->get('/office/payment-proofs/'.$name)->assertOk();

    @unlink(storage_path('app/private/payment-proofs/'.$name));
});
