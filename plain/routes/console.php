<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('orders:cancel-unpaid')->hourly();
Schedule::command('spammers:prune-expired')->hourly()->withoutOverlapping(30);
Schedule::command('activity-logs:prune')->dailyAt('03:00')->timezone('Asia/Jakarta');
