<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_box_batch_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('split_box_batches')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('split_box_slots')->cascadeOnDelete();
            $table->unsignedSmallInteger('qty_total');
            $table->unsignedSmallInteger('qty_taken')->default(0);
            $table->timestamps();

            $table->unique(['batch_id', 'slot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_box_batch_slots');
    }
};
