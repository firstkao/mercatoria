<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_tiers')) return;

        Schema::create('shipping_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->decimal('fee_yuan', 10, 2)->default(0);
            $table->decimal('min_purchase_yuan', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_tiers');
    }
};