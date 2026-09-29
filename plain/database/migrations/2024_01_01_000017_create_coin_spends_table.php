<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coin_spends')) return;

        Schema::create('coin_spends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_lot_id')->constrained('coin_lots')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('applied');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_spends');
    }
};