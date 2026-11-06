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

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100);
            $table->unsignedTinyInteger('modifier')->default(0); // 0, 1, atau 2
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        
            $table->index(['product_attribute_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
    }
};
