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
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
