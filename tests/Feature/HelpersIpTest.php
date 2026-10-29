<?php

use Illuminate\Http\Request;

test('get_real_ip memakai X-Forwarded-For prioritas pertama', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_X_FORWARDED_FOR' => '203.0.113.5, 10.0.0.1',
        'HTTP_X_REAL_IP' => '198.51.100.7',
        'REMOTE_ADDR' => '192.0.2.1',
    ]);

    expect(get_real_ip($request))->toBe('203.0.113.5');
});

test('get_real_ip skip private dan reserved lalu fallback', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 127.0.0.1', // semua privat
        'HTTP_X_REAL_IP' => '198.51.100.7',
        'REMOTE_ADDR' => '192.0.2.1',
    ]);

    expect(get_real_ip($request))->toBe('198.51.100.7');

    $tanpaHeader = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.9']);
    expect(get_real_ip($tanpaHeader))->toBe('192.0.2.9');
});

test('get_real_ip mengabaikan nilai tidak valid', function () {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_X_FORWARDED_FOR' => 'bukan-ip, 203.0.113.9',
        'REMOTE_ADDR' => '192.0.2.1',
    ]);

    expect(get_real_ip($request))->toBe('203.0.113.9');
});

test('ip_in_cidr memvalidasi range ipv4 dan ipv6', function () {
    expect(ip_in_cidr('192.168.1.50', '192.168.1.0/24'))->toBeTrue()
        ->and(ip_in_cidr('192.168.2.50', '192.168.1.0/24'))->toBeFalse()
        ->and(ip_in_cidr('203.0.113.5', '203.0.113.5/32'))->toBeTrue()
        ->and(ip_in_cidr('2001:db8::1', '2001:db8::/32'))->toBeTrue()
        ->and(ip_in_cidr('2001:db9::1', '2001:db8::/32'))->toBeFalse();
});

test('ip_in_cidr menolak input tidak valid', function () {
    expect(fn () => ip_in_cidr('bukan-ip', '192.168.1.0/24'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => ip_in_cidr('192.168.1.1', 'bukan-cidr'))->toThrow(InvalidArgumentException::class);
});
