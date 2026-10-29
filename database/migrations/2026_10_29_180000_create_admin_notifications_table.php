<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRD 41.1: bell notifikasi admin. Retensi 90 hari (notification:purge).
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 48);
            $table->string('title');
            $table->string('target_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
