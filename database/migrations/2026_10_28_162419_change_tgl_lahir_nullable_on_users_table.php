<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // User migrasi lama mengisi tgl lahir via /profile/complete (Skema DB 16.2).
            $table->date('tgl_lahir')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('tgl_lahir')->nullable(false)->change();
        });
    }
};
