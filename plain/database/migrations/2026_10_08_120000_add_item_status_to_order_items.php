<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Status per item. NULL = ikut status pesanan global.
            // Kalau diisi, item ini punya status sendiri yang beda dari parent.
            $table->string('item_status', 30)
                ->nullable()
                ->after('quantity');

            $table->timestamp('item_status_updated_at')
                ->nullable()
                ->after('item_status');

            $table->index('item_status');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['item_status']);
            $table->dropColumn(['item_status', 'item_status_updated_at']);
        });
    }
};
