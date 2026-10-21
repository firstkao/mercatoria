<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_state', 16)->default('mvp');
            $table->timestamp('state_updated_at')->nullable();
            $table->string('tier', 16)->default('silver');
            $table->decimal('total_belanja', 14, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->timestamp('unblocked_until')->nullable();
            $table->timestamps();
            $table->index(['current_state']);
            $table->index(['tier']);
            $table->index(['total_belanja']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
