<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem anti-spam klik referral diubah: dari "1x per IP per HARI" menjadi
 * "1x per IP PER SELAMANYA (seumur hidup) per kode pengajak".
 *
 * Kenapa: dengan aturan harian, satu orang bisa berulang kali membuka link
 * dari perangkat yang sama tiap hari dan pengajak dapat +10 koin terus —
 * padahal "teman"nya ya orang yang sama. Sekarang 1 IP hanya dihitung sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referral_clicks')) {
            return;
        }

        // 1) Bersihkan duplikat historis: untuk pasangan (referrer, ip),
        //    pertahankan klik PERTAMA (yang dulu benar-benar memberi reward),
        //    hapus klik-klik berikutnya dari hari-hari setelahnya.
        DB::statement(
            'DELETE t1 FROM referral_clicks t1
             INNER JOIN referral_clicks t2
             ON  t1.referrer_id = t2.referrer_id
             AND t1.ip_address  = t2.ip_address
             AND t1.id > t2.id'
        );

        // 2) Ganti unique index harian menjadi unique seumur hidup per (referrer, ip).
        Schema::table('referral_clicks', function (Blueprint $table) {
            try {
                $table->dropUnique('referral_clicks_unique_daily');
            } catch (\Throwable $e) {
                // Index mungkin sudah tidak ada (DB dibuat dari dump lama) -> lanjut saja.
            }
        });

        Schema::table('referral_clicks', function (Blueprint $table) {
            $table->unique(['referrer_id', 'ip_address'], 'referral_clicks_unique_per_ip');
            $table->index('referrer_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('referral_clicks')) {
            return;
        }

        Schema::table('referral_clicks', function (Blueprint $table) {
            try {
                $table->dropUnique('referral_clicks_unique_per_ip');
            } catch (\Throwable $e) {
                // abaikan
            }
            $table->unique(['referrer_id', 'ip_address', 'clicked_on'], 'referral_clicks_unique_daily');
        });
    }
};
