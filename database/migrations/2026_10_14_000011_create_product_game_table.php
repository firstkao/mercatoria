<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_game');
    }
};
