<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal cron (PRD 37.3–39.10, WIB) — timezone Asia/Jakarta.
Schedule::command('log:purge')->dailyAt('03:00')->timezone('Asia/Jakarta');
Schedule::command('notification:purge')->dailyAt('03:30')->timezone('Asia/Jakarta');
Schedule::command('user:birthday-bonus')->dailyAt('08:00')->timezone('Asia/Jakarta');
Schedule::command('coin:expire')->dailyAt('08:10')->timezone('Asia/Jakarta');
Schedule::command('coin:expiry-notify')->dailyAt('08:20')->timezone('Asia/Jakarta');
Schedule::command('splitbox:check-batches')->dailyAt('08:30')->timezone('Asia/Jakarta');

// Timeout order (PRD 37.3/39.10) — wajib jalan agar status & forfeit koin/voucher aktif.
Schedule::command('orders:expire-unpaid')->everyFifteenMinutes()->timezone('Asia/Jakarta');
Schedule::command('orders:expire-penalties')->everyFifteenMinutes()->timezone('Asia/Jakarta');
Schedule::command('orders:hangus-expired')->dailyAt('05:00')->timezone('Asia/Jakarta');
