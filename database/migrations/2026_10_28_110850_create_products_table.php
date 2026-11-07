<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ── Foreign keys ──
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('developer_id')->nullable()->constrained('developers')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('merch_category_id')->nullable()->constrained('merch_categories')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('products')->nullOnDelete();

            // ── Product info ──
            $table->string('name', 150);
            $table->string('sku', 64)->unique();
            $table->string('slug', 170)->nullable()->unique();
            $table->decimal('price_rmb', 10, 2);
            $table->integer('weight_gram')->nullable();
            $table->string('type', 16)->default('single');
            $table->string('image_path')->nullable();
            $table->string('description_image_path')->nullable();

            // ── Discount ──
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->date('discount_start')->nullable();
            $table->date('discount_end')->nullable();

            // ── Status ──
            $table->string('availability', 16)->default('tersedia');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sold_count')->default(0);
            $table->boolean('is_split_box')->default(false);

            $table->timestamps();

            // ── Indexes ──
            $table->index(['store_id']);
            $table->index(['game_id']);
            $table->index(['developer_id']);
            $table->index(['is_active', 'game_id']);
            $table->index(['parent_id']);
            $table->index(['is_split_box', 'is_active']);
            $table->index(['merch_category_id', 'is_active']);
        });

        // 2. Tabel Product Variants
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

        // 3. Tabel Product Attributes
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'position']);
        });

        // 4. Tabel Product Attribute Values
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
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
