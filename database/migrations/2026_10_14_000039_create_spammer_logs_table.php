<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spammer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spammer_id')->constrained()->cascadeOnDelete();
            $table->string('action', 32);
            $table->string('ip_address', 45)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spammer_logs');
    }
};
