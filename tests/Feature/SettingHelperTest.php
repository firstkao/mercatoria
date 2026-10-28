<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

test('setting mengambil nilai dari database', function () {
    Setting::create([
        'key' => 'rate_rmb_idr',
        'value' => '3000',
        'type' => 'int',
        'group' => 'pricing',
        'description' => 'Rate RMB → IDR',
    ]);

    expect(setting('rate_rmb_idr'))->toBe('3000');
});

test('setting mengembalikan default bila key tidak ada', function () {
    expect(setting('key_yang_tidak_ada', 'fallback'))->toBe('fallback')
        ->and(setting('key_yang_tidak_ada'))->toBeNull();
});

test('setting menyimpan cache antar permintaan', function () {
    Setting::create([
        'key' => 'profit_percent',
        'value' => '11',
        'type' => 'int',
        'group' => 'pricing',
        'description' => 'Profit',
    ]);

    expect(setting('profit_percent'))->toBe('11');

    Setting::where('key', 'profit_percent')->update(['value' => '15']);

    expect(setting('profit_percent'))->toBe('11');

    Cache::flush();

    expect(setting('profit_percent'))->toBe('15');
});
