<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 64)->unique();
            $table->foreignId('store_id')->nullable()
                ->constrained('stores')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('description_image_path')->nullable();
            $table->string('main_image_path')->nullable();
            $table->string('type', 16)->default('single');
            $table->string('availability', 16)->default('tersedia');
            $table->integer('weight_gram')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price_rmb', 10, 2)->default(0);
            $table->decimal('shipping_cn_rmb', 10, 2)->default(0);
            $table->decimal('shipping_cn_btm_idr', 14, 2)->default(0);
            $table->decimal('shipping_btm_jkt_idr', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->nullable();
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();
            $table->integer('sold_count')->default(0);
            $table->timestamps();
            $table->index(['is_active']);
            $table->index(['availability']);
            $table->index(['discount_amount']);
            $table->index(['sold_count']);
            $table->index(['created_at']);
            $table->index(['store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
