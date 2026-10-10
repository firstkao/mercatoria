<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_boxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->unique()
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('requester_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Harga per slot — SAMA untuk semua karakter
            $table->unsignedBigInteger('price_per_slot_idr');

            $table->timestamp('deadline_at')->nullable();

            // published / locked / cancelled / fulfilled
            $table->string('status', 20)->default('published');

            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_boxes');
    }
};
