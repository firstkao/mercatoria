<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('orders', 'orders_status_created_at_index')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            });
        }

        if (! Schema::hasIndex('order_items', 'order_items_order_id_index')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->index('order_id');
            });
        }

        if (! Schema::hasIndex('product_variants', 'product_variants_product_id_index')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->index('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('orders', 'orders_status_created_at_index')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropIndex('orders_status_created_at_index');
            });
        }

        // NOTE: order_items_order_id_index dan product_variants_product_id_index
        // adalah index pendukung FOREIGN KEY yang dibuat oleh migrasi tabel dasar
        // (create_order_items_table / create_product_variants_table), BUKAN oleh
        // migrasi ini. Men-drop-nya di sini akan gagal dengan MySQL error 1553
        // ("needed in a foreign key constraint"), jadi sengaja dibiarkan.
    }
};