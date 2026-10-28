<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_box_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('split_box_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('batch_no');
            $table->string('status', 16)->default('open');
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_auto_opened')->default(false);
            $table->timestamps();

            $table->unique(['split_box_id', 'batch_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_box_batches');
    }
};
