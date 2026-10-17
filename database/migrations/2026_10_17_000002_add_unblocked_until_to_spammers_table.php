<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spammers', function (Blueprint $table) {
            $table->timestamp('unblocked_until')->nullable()->after('unblock_count');
            $table->index(['status', 'unblocked_until']);
        });
    }

    public function down(): void
    {
        Schema::table('spammers', function (Blueprint $table) {
            $table->dropIndex(['status', 'unblocked_until']);
            $table->dropColumn('unblocked_until');
        });
    }
};
