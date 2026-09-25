<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SKU holds the Taobao/Tmall item name, often in Chinese, so it is free text and not unique.
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('slug');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('sku');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};