<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('name_blacklist')) return;

        Schema::create('name_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_normalized')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('name_blacklist');
    }
};