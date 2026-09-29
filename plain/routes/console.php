<?php

use Illuminate\Support\Facades\Schedule;

// Kirim email peringatan setiap jam (untuk order umur 20 jam)
Schedule::command('orders:send-reminders')->hourly();

// Batalkan pesanan yang belum dibayar (tiap 15 menit)
Schedule::command('orders:cancel-unpaid')->everyFifteenMinutes()->withoutOverlapping();

// Bersihkan data spammer setiap jam
Schedule::command('spammers:prune-expired')->hourly()->withoutOverlapping(30);

// Bersihkan log aktivitas setiap hari jam 03:00 WIB
Schedule::command('activity-logs:prune')->dailyAt('03:00')->timezone('Asia/Jakarta');

// Siklus koin (ulang tahun, hangus, reminder)
Schedule::command('coins:lifecycle')->dailyAt('01:00')->timezone('Asia/Jakarta');

// Generate voucher ulang tahun
Schedule::command('vouchers:birthday')->dailyAt('00:05')->timezone('Asia/Jakarta');

// Backup database setiap hari jam 02:00 WIB
Schedule::command('backup:database --keep=7')
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

// Cleanup storage setiap Minggu jam 03:00 WIB
Schedule::command('storage:cleanup')
    ->weeklyOn(0, '03:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

// Refresh ranking best seller setiap hari jam 04:00 WIB (Batch 30)
Schedule::command('products:refresh-best-sellers')
    ->dailyAt('04:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

// Cart abandonment reminder tiap jam (Batch 31)
Schedule::command('carts:send-reminders')
    ->hourly()
    ->withoutOverlapping();