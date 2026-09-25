<?php

use Illuminate\Support\Facades\Schedule;

// Kirim email peringatan setiap jam (untuk order umur 20 jam)
Schedule::command('orders:send-reminders')->hourly();
Schedule::command('orders:cancel-unpaid')->hourly();
Schedule::command('spammers:prune-expired')->hourly()->withoutOverlapping(30);
Schedule::command('activity-logs:prune')->dailyAt('03:00')->timezone('Asia/Jakarta');
