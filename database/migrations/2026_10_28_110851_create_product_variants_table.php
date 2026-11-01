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
            $table->string('sku', 64)->unique();
            $table->decimal('price_rmb', 10, 2)->nullable();
            $table->integer('weight_gram')->nullable();
            $table->string('availability', 16)->default('tersedia');
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('name', 'pv_name_idx');
            $table->index(['product_id', 'availability'], 'pv_product_avail_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
