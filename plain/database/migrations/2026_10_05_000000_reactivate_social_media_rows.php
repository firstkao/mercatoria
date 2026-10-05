<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug fix: semua baris social_media sempat ber-status is_active = 0
     * (akibat bug penyimpanan form Pengaturan Umum), sehingga ikon sosial
     * media tidak muncul sama sekali di header/footer layout publik.
     *
     * Baris yang punya URL adalah milik user sungguhan (Instagram, Facebook,
     * X, Threads, WhatsApp) — aktifkan kembali. Baris tanpa URL dibiarkan
     * nonaktif agar tidak dirender sebagai ikon mati.
     */
    public function up(): void
    {
        if (! Schema::hasTable('social_media')) {
            return;
        }

        DB::table('social_media')
            ->whereNull('is_active')
            ->update(['is_active' => false]);

        DB::table('social_media')
            ->whereNotNull('url')
            ->update(['is_active' => true]);
    }

    public function down(): void
    {
        // Tidak di-rollback: status nonaktif sebelumnya adalah kondisi bug,
        // bukan konfigurasi yang ingin dipertahankan.
    }
};
