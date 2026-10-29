<?php

use Illuminate\Console\Scheduling\Schedule;

test('tiga command timeout order terdaftar di schedule', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('orders:expire-unpaid')
        ->expectsOutputToContain('orders:hangus-expired')
        ->expectsOutputToContain('orders:expire-penalties')
        ->assertSuccessful();
});

test('jadwal timeout order memakai timezone asia jakarta', function () {
    $events = collect(app(Schedule::class)->events());

    $find = fn (string $name) => $events->first(
        fn ($event) => str_contains((string) $event->command, $name)
    );

    expect($find('orders:expire-unpaid')->timezone)->toBe('Asia/Jakarta')
        ->and($find('orders:hangus-expired')->timezone)->toBe('Asia/Jakarta')
        ->and($find('orders:expire-penalties')->timezone)->toBe('Asia/Jakarta');
});

test('frekuensi jadwal sesuai ketentuan', function () {
    $events = collect(app(Schedule::class)->events());

    $find = fn (string $name) => $events->first(
        fn ($event) => str_contains((string) $event->command, $name)
    );

    // expire-unpaid & expire-penalties tiap 15 menit; hangus-expired 05:00 harian
    expect($find('orders:expire-unpaid')->expression)->toBe('*/15 * * * *')
        ->and($find('orders:expire-penalties')->expression)->toBe('*/15 * * * *')
        ->and($find('orders:hangus-expired')->expression)->toBe('0 5 * * *');
});

test('jam cron sesuai prd 37.3', function () {
    $events = collect(app(Schedule::class)->events());

    $find = fn (string $name) => $events->first(
        fn ($event) => str_contains((string) $event->command, $name)
    );

    // PRD 37.3: birthday 07:00, expiry-notify 08:00, sitemap 06:00
    expect($find('user:birthday-bonus')->expression)->toBe('0 7 * * *')
        ->and($find('coin:expiry-notify')->expression)->toBe('0 8 * * *')
        ->and($find('sitemap:generate')->expression)->toBe('0 6 * * *');
});

test('tiga command timeout berjalan sukses saat dipanggil manual', function () {
    $this->artisan('orders:expire-unpaid')->assertSuccessful();
    $this->artisan('orders:hangus-expired')->assertSuccessful();
    $this->artisan('orders:expire-penalties')->assertSuccessful();
});
