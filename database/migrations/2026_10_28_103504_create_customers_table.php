<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('no_hp', 20);
            $table->string('current_state', 16)->default('MVP');
            $table->unsignedTinyInteger('unblock_count')->default(0);
            $table->timestamp('unblock_grace_until')->nullable();
            $table->string('tier', 16)->default('silver');
            $table->decimal('total_belanja', 15, 2)->default(0);
            $table->timestamp('state_updated_at')->nullable();
            $table->timestamps();

            $table->index('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
