<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dropdown berantai alamat: tautan ke indonesia_regions (nama tetap di kolom string).
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('provinsi_id')->nullable()->after('provinsi')->constrained('indonesia_regions')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->after('kota')->constrained('indonesia_regions')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->after('kecamatan')->constrained('indonesia_regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provinsi_id');
            $table->dropConstrainedForeignId('kota_id');
            $table->dropConstrainedForeignId('kecamatan_id');
        });
    }
};
