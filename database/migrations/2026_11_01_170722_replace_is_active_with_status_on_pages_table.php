<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('status', 10)->default('draft');
        });

        DB::table('pages')->where('is_active', true)->update(['status' => 'publish']);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        DB::table('pages')->where('status', '!=', 'publish')->update(['is_active' => false]);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
