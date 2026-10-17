<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()
                ->constrained('product_variants')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->text('customer_note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'product_id', 'variant_id'], 'cart_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('carts'); }
};
