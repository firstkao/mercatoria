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

            $table->foreignId('split_box_id')
                ->constrained('split_boxes')
                ->cascadeOnDelete();

            // Nama karakter + gambar — disimpen langsung (bukan link variant)
            $table->string('character_name', 100);
            $table->string('character_image', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Wajib +1 slot lain sebagai pasangan?
            $table->boolean('bundle_required')->default(false);

            // available / locked / approved
            $table->string('status', 20)->default('available');

            $table->foreignId('locked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('locked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['split_box_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_box_slots');
    }
};
