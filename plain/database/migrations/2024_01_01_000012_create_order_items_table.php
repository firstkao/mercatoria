<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) return;

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->string('variant_name_snapshot');
            $table->decimal('price_yuan_snapshot', 10, 2);
            $table->unsignedInteger('weight_grams_snapshot');
            $table->decimal('cn_shipping_yuan_snapshot', 10, 2)->default(0);
            $table->unsignedInteger('unit_price_idr');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('line_total_idr');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};