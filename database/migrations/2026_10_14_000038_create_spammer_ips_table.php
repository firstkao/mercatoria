<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spammer_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spammer_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->integer('attempt_count')->default(1);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
            $table->index(['ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spammer_ips');
    }
};
