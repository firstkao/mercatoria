<?php

use App\Models\OrderStatus;

test('semua 16 status order punya label dan warna di satu sumber', function () {
    $map = config('order_statuses');

    expect(OrderStatus::cases())->toHaveCount(16);

    foreach (OrderStatus::cases() as $status) {
        expect($map[$status->value] ?? null)->not->toBeNull()
            ->and($map[$status->value]['label'] ?? '')->not->toBe('')
            ->and($map[$status->value]['color'] ?? '')->not->toBe('');
    }
});

test('label status memakai nama kanonik', function () {
    expect(order_status_label('ditahan'))->toBe('Menunggu Verifikasi')
        ->and(order_status_label('menunggu_pelunasan_pengambilan'))->toBe('Menunggu Pelunasan & Pengambilan')
        ->and(order_status_label('menunggu_denda'))->toBe('Menunggu Denda')
        ->and(order_status_label('hangus_permanen'))->toBe('Hangus Permanen');
});

test('warna status berbeda untuk nasib baik dan buruk', function () {
    expect(order_status_color('selesai'))->toContain('green')
        ->and(order_status_color('pembayaran_gagal'))->toContain('red')
        ->and(order_status_color('menunggu_pembayaran'))->toContain('amber');
});

test('status tak dikenal tetap punya fallback label dan warna', function () {
    expect(order_status_label('status_ngawur'))->toBe('Status Ngawur')
        ->and(order_status_color('status_ngawur'))->not->toBe('');
});

test('enum label dan helper satu sumber', function () {
    foreach (OrderStatus::cases() as $status) {
        expect($status->label())->toBe(order_status_label($status->value));
    }
});

test('slot_sold punya label dan warna merah di satu sumber', function () {
    expect(order_status_label('slot_sold'))->toBe('Slot Sold')
        ->and(order_status_color('slot_sold'))->toContain('red');
});
