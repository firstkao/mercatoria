<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_box_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('split_box_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('split_role', 16)->default('normal');
            $table->unsignedSmallInteger('qty_per_box')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['split_box_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_box_slots');
    }
};
