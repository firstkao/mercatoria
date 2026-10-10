<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'split_box_slot_id')) {
                $table->foreignId('split_box_slot_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('split_box_slots')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'split_box_slot_id')) {
                $table->dropForeign(['split_box_slot_id']);
                $table->dropColumn('split_box_slot_id');
            }
        });
    }
};
