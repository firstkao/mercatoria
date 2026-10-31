<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 7.19: kuota view 10 produk untuk Crush/Ex/ONS — 1 produk = 1 hitungan.
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // [FIX #1] Default ke now() — aman kalau recordView() lupa pass.
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            // 1 produk = 1 hitungan per user.
            $table->unique(['user_id', 'product_id']);

            // [FIX #3] Index untuk query kuota: WHERE user_id = ? AND viewed_at >= ?
            $table->index(['user_id', 'viewed_at'], 'product_views_user_viewed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};