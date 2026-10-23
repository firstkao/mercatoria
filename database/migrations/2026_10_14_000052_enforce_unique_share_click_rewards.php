<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            // 1. Jika constraint atau index 'share_unique' terikat foreign key, 
            // drop foreign key-nya dulu jika ada (sesuaikan nama constraint jika perlu, 
            // atau gunakan try-catch / dropForeign jika nama constraint-nya standar).
            // Contoh standar Laravel: nama foreign key biasanya 'share_clicks_user_id_foreign' atau sejenisnya,
            // tapi jika itu murni index unique, pastikan apakah itu foreign key atau index biasa.
            
            // Jika errornya bilang "needed in a foreign key constraint", berarti ada tabel lain 
            // yang melakukan foreign key ke kolom ini menggunakan index tersebut.
            // Kita drop dulu foreign key yang mengarah ke sini, atau drop index-nya dengan aman:

            try {
                $table->dropForeign(['user_id']); // Sesuaikan kolom yang berelasi jika ada
            } catch (\Exception $e) {
                // Abaikan jika bukan foreign key langsung
            }

            // 2. Hapus index 'share_unique' jika ada
            if (Schema::hasIndex('share_clicks', 'share_unique')) {
                $table->dropIndex('share_unique');
            }

            // 3. Tambahkan unique index yang baru
            $table->unique(['user_id', 'product_id', 'ip_address'], 'share_clicks_unique');
        });
    }

    public function down(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            // Lakukan rollback ke kondisi semula
            if (Schema::hasIndex('share_clicks', 'share_clicks_unique')) {
                $table->dropUnique('share_clicks_unique');
            }

            $table->index(['user_id', 'product_id', 'ip_address'], 'share_unique');
        });
    }
};