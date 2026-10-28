<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item_snapshots', function (Blueprint $table) {
            $table->string('item_type', 8)->default('single')->after('variant_id_audit');
            $table->uuid('bundle_group_id')->nullable()->after('item_type');
            $table->string('split_role', 16)->nullable()->after('bundle_group_id');
            $table->unsignedSmallInteger('split_batch_no')->nullable()->after('split_role');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_snapshots', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'bundle_group_id', 'split_role', 'split_batch_no']);
        });
    }
};
