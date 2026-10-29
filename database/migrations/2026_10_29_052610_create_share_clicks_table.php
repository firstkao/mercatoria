<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 9.5: share link produk — koin per IP unik per link (Indonesia saja).
        Schema::create('share_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('country_code', 2)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_clicks');
    }
};
