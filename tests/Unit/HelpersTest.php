<?php

test('format_rp memformat angka ke rupiah', function () {
    expect(format_rp(255000))->toBe('Rp255.000')
        ->and(format_rp(0))->toBe('Rp0')
        ->and(format_rp(1500000.75))->toBe('Rp1.500.001')
        ->and(format_rp('1000'))->toBe('Rp1.000');
});

test('normalize_phone menyeragamkan nomor ke format 62xxx', function () {
    expect(normalize_phone('081234567890'))->toBe('6281234567890')
        ->and(normalize_phone('6281234567890'))->toBe('6281234567890')
        ->and(normalize_phone('+6281234567890'))->toBe('6281234567890')
        ->and(normalize_phone('0812-3456-7890'))->toBe('6281234567890');
});
