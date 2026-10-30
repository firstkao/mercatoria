<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kode wilayah resmi (Kemendagri) per level, dari dataset sumber impor.
        Schema::table('indonesia_regions', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('name');
            $table->index(['type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('indonesia_regions', function (Blueprint $table) {
            $table->dropIndex(['type', 'code']);
            $table->dropColumn('code');
        });
    }
};
