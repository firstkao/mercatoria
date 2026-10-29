<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal cron (PRD 39.7–39.10, WIB) — timezone Asia/Jakarta.
Schedule::command('log:purge')->dailyAt('03:00')->timezone('Asia/Jakarta');
Schedule::command('notification:purge')->dailyAt('03:30')->timezone('Asia/Jakarta');
Schedule::command('user:birthday-bonus')->dailyAt('08:00')->timezone('Asia/Jakarta');
Schedule::command('coin:expire')->dailyAt('08:10')->timezone('Asia/Jakarta');
Schedule::command('coin:expiry-notify')->dailyAt('08:20')->timezone('Asia/Jakarta');
Schedule::command('splitbox:check-batches')->dailyAt('08:30')->timezone('Asia/Jakarta');
