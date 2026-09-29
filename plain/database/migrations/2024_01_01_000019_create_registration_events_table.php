<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registration_events')) return;

        Schema::create('registration_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('identity_record_id')->constrained('identity_records')->cascadeOnDelete();
            $table->string('event', 30);
            $table->timestamp('created_at')->nullable();

            $table->index('identity_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_events');
    }
};