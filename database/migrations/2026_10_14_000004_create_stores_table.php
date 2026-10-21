<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('min_belanja_rmb', 10, 2)->default(0);
            $table->decimal('ongkir_rmb', 10, 2)->default(0);
            $table->timestamps();
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
