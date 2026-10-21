<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            // Hanya hapus index 'share_unique' jika index tersebut benar-benar ada di database
            if (Schema::hasIndex('share_clicks', 'share_unique')) {
                $table->dropIndex('share_unique');
            }

            // Tambahkan unique index yang baru
            $table->unique(['user_id', 'product_id', 'ip_address'], 'share_clicks_unique');
        });
    }

    public function down(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            // Lakukan hal yang sama untuk fungsi rollback (down) agar aman jika di-rollback berkali-kali
            if (Schema::hasIndex('share_clicks', 'share_clicks_unique')) {
                $table->dropUnique('share_clicks_unique');
            }

            $table->index(['user_id', 'product_id', 'ip_address'], 'share_unique');
        });
    }
};
