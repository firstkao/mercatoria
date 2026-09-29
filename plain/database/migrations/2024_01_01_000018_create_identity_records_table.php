<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('identity_records')) return;

        Schema::create('identity_records', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->string('value');
            $table->unsignedSmallInteger('deletion_count')->default(0);
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->unique(['kind', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_records');
    }
};