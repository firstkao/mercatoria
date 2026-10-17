<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variant_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('attribute_key', 32);
            $table->string('attribute_value', 64);
            $table->timestamps();
            $table->index(['variant_id', 'attribute_key']);
        });
    }
    public function down(): void { Schema::dropIfExists('variant_combinations'); }
};
