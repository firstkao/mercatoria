<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merch_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()
                ->constrained('merch_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('icon_path')->nullable();
            $table->json('keyword_list')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merch_categories');
    }
};
