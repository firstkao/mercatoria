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
            // [FIX #1] Persentase ongkir lokal China — dipakai product form & kalkulasi harga.
            $table->decimal('ongkir_lokal_cn_percent', 5, 2)->default(7);
            $table->timestamps();

            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};