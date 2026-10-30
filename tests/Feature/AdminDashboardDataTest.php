<?php

use App\Models\User;

test('dashboard admin mengirim data widget ds 14.2', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/office')->assertOk();

    $aksi = $response->viewData('pesananMenungguAksi');

    expect($aksi)->toBeArray()
        ->and(array_keys($aksi))->toContain('verifikasi_bukti', 'refund_pending', 'spammer_baru', 'order_baru', 'hampir_hangus')
        ->and($response->viewData('notifikasiTerbaru'))->toHaveCount(0);
});
