<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'updated_at'], 'orders_status_updated_at_index');
            $table->index(['user_id', 'status', 'updated_at'], 'orders_user_status_updated_at_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'verified_at'], 'payments_status_verified_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_verified_at_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_updated_at_index');
            $table->dropIndex('orders_user_status_updated_at_index');
        });
    }
};
