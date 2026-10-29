<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Masa berlaku template (hari) — dipakai saat issue user_voucher (PRD 9.6).
            $table->unsignedInteger('valid_days')->nullable()->after('min_belanja');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('valid_days');
        });
    }
};
