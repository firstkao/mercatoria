<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Batas waktu upload bukti bayar (PRD 17.10 — 1×24 jam, direset saat ditolak).
            $table->timestamp('payment_deadline_at')->nullable()->after('hangus_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_deadline_at');
        });
    }
};
