<?php

use Illuminate\Support\Facades\Schedule;

// Kirim email peringatan setiap jam (untuk order umur 20 jam)
Schedule::command('orders:send-reminders')->hourly();

// Batalkan pesanan yang belum dibayar 
// (Di-mix menggunakan versi terbaik: tiap 15 menit & tidak boleh tumpang tindih)
Schedule::command('orders:cancel-unpaid')->everyFifteenMinutes()->withoutOverlapping();

// Bersihkan data spammer setiap jam (maksimal waktu tunggu 30 menit jika masih proses)
Schedule::command('spammers:prune-expired')->hourly()->withoutOverlapping(30);

// Bersihkan log aktivitas setiap hari jam 03:00 pagi waktu Jakarta
Schedule::command('activity-logs:prune')->dailyAt('03:00')->timezone('Asia/Jakarta');
