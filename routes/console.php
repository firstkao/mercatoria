<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:send-pickup-reminders')->hourly();
Schedule::command('orders:hangus-expired')->hourly();
Schedule::command('log:purge')->dailyAt('03:00');
Schedule::command('notification:purge')->dailyAt('03:30');
Schedule::command('user:update-state')->dailyAt('02:00');
Schedule::command('customer:update-tier')->dailyAt('02:30');
Schedule::command('user:purge-crush')->dailyAt('04:00');
Schedule::command('cart:send-reminder')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('coin:expiry-notify')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('user:birthday-bonus')->dailyAt('08:15')->withoutOverlapping();
Schedule::command('spammers:recheck-grace')->dailyAt('04:30');
Schedule::command('sitemap:generate')->dailyAt('05:00');
