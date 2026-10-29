<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 43.7: isi pesan notifikasi (untuk notif manual admin & lainnya).
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->text('message')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
};
