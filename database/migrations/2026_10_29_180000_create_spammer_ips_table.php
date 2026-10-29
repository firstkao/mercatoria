<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 8.5/10.5: Smart Scoped IP + Device Ban, TTL 30 hari.
        Schema::create('spammer_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('device_fingerprint', 128)->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spammer_ips');
    }
};
