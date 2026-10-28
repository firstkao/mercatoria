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
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('merch_category_id')->nullable()->constrained('merch_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('sku', 64)->unique();
            $table->string('slug', 170)->unique();
            $table->decimal('price_rmb', 10, 2);
            $table->integer('weight_gram')->nullable();
            $table->string('type', 16)->default('single');
            $table->string('image_path')->nullable();
            $table->string('description_image_path')->nullable();
            $table->text('description')->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->date('discount_start')->nullable();
            $table->date('discount_end')->nullable();
            $table->string('availability', 16)->default('tersedia');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();

            $table->index(['store_id']);
            $table->index(['game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
