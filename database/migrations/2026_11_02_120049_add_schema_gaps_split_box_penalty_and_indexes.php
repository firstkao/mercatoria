<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('penalty_per_day', 14, 2)->nullable()->after('penalty_amount');
            $table->unsignedInteger('penalty_days')->nullable()->after('penalty_per_day');
            $table->index(['status', 'penalty_deadline_at']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('split_box_slot_id')->nullable()->after('variant_id')
                ->constrained('split_box_slots')->nullOnDelete();
            $table->index('bundle_group_id');
        });

        Schema::table('split_box_batches', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->index(['split_box_id', 'status']);
        });

        Schema::table('split_box_slots', function (Blueprint $table) {
            $table->index(['split_box_id', 'split_role']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('bundle_group_id');
        });

        Schema::table('order_item_snapshots', function (Blueprint $table) {
            $table->index('item_type');
            $table->index('bundle_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_snapshots', function (Blueprint $table) {
            $table->dropIndex(['item_type']);
            $table->dropIndex(['bundle_group_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['bundle_group_id']);
        });

        Schema::table('split_box_slots', function (Blueprint $table) {
            $table->dropIndex(['split_box_id', 'split_role']);
        });

        Schema::table('split_box_batches', function (Blueprint $table) {
            $table->dropIndex(['split_box_id', 'status']);
            $table->dropColumn(['opened_at', 'closed_at']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['bundle_group_id']);
            $table->dropConstrainedForeignId('split_box_slot_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'penalty_deadline_at']);
            $table->dropColumn(['penalty_per_day', 'penalty_days']);
        });
    }
};
