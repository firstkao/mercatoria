<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ReferralClick sekarang khusus "share link produk".
 *
 *  - Baris lama (product_id NULL) = klik link undang teman (legacy, gak dapat reward).
 *  - Baris baru (product_id terisi) = klik link produk (dapat reward click).
 *
 * Unique index diganti dari (referrer, ip) → (referrer, product, ip)
 * supaya orang boleh klik produk A dan B dari referrer sama, tapi
 * gak boleh klik produk A dua kali dari IP yang sama.
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

            try {
                $table->dropUnique('referral_clicks_unique_per_ip');
            } catch (\Throwable $e) {
                // mungkin sudah beda nama / sudah tidak ada → lanjut
            }

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
