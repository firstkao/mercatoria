<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ReferralClick sekarang khusus untuk "share link produk".
 *
 * Perubahan:
 *  - tambah product_id (FK ke products)
 *  - unique index lama (referrer_id, ip_address) → diganti jadi
 *    (referrer_id, product_id, ip_address) supaya:
 *      * orang yang sama boleh klik produk A dan produk B milik referrer sama
 *      * tapi tidak boleh klik produk A dua kali dari IP sama
 *
 * Catatan: baris lama (klik link undang teman, product_id NULL) tetap
 * dibiarkan sebagai history; gak akan bentrok dengan unique baru karena
 * MySQL menganggap NULL berbeda satu sama lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_clicks', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->nullable()
                ->after('referrer_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Drop unique lama
            try {
                $table->dropUnique('referral_clicks_unique_per_ip');
            } catch (\Throwable $e) {
                // Index mungkin sudah beda nama / sudah hilang → lanjut
            }

            // Unique baru: 1 referrer × 1 produk × 1 IP = 1x seumur hidup
            $table->unique(
                ['referrer_id', 'product_id', 'ip_address'],
                'referral_clicks_unique_per_ip_product'
            );
        });
    }

    public function down(): void
    {
        Schema::table('referral_clicks', function (Blueprint $table) {
            try {
                $table->dropUnique('referral_clicks_unique_per_ip_product');
            } catch (\Throwable $e) {
                // abaikan
            }

            $table->unique(['referrer_id', 'ip_address'], 'referral_clicks_unique_per_ip');

            $table->dropConstrainedForeignId('product_id');
        });
    }
};
