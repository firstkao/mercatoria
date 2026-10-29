<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Field kelola admin (PRD 22.6): tipe & fee per metode.
            $table->string('type', 30)->nullable()->after('name');
            $table->decimal('fee', 12, 2)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'fee']);
        });
    }
};
