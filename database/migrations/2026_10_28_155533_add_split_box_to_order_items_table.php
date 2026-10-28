<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->uuid('bundle_group_id')->nullable()->after('variant_id');
            $table->foreignId('split_box_slot_id')->nullable()->constrained('split_box_slots')->nullOnDelete();
            $table->foreignId('split_box_batch_slot_id')->nullable()->constrained('split_box_batch_slots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('split_box_batch_slot_id');
            $table->dropConstrainedForeignId('split_box_slot_id');
            $table->dropColumn('bundle_group_id');
        });
    }
};
