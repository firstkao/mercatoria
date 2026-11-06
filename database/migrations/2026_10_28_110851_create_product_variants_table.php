<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('sku', 64)->nullable()->unique();
            $table->decimal('price_rmb', 10, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->date('discount_start')->nullable();
            $table->date('discount_end')->nullable();
            $table->integer('weight_gram')->nullable();
            $table->string('availability', 16)->default('tersedia');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('name', 'pv_name_idx');
            $table->index(['product_id', 'availability'], 'pv_product_avail_idx');
        });

        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_value_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_variant_id', 'product_attribute_value_id'], 'pvav_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
    }
};
