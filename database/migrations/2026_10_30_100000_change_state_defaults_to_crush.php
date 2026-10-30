<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 7.1: user baru = Crush; MVP/Ex/ONS/Blocked hanya setelah jadi Customer.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('crush')->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('current_state', 16)->default('crush')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('current_state', 16)->default('MVP')->change();
        });
    }
};
