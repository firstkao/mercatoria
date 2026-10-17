<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            $table->dropIndex('share_unique');
            $table->unique(['user_id', 'product_id', 'ip_address'], 'share_clicks_unique');
        });
    }

    public function down(): void
    {
        Schema::table('share_clicks', function (Blueprint $table): void {
            $table->dropUnique('share_clicks_unique');
            $table->index(['user_id', 'product_id', 'ip_address'], 'share_unique');
        });
    }
};
