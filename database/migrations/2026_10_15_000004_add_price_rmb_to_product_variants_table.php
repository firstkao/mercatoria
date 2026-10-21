<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price_rmb', 10, 2)->nullable()->after('sku');
            $table->index('name', 'product_variants_name_search_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('product_variants_name_search_idx');
            $table->dropColumn('price_rmb');
        });
    }
};
