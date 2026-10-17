<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('source', 32);
            $table->string('description')->nullable();
            $table->integer('balance_after');
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['expires_at']);
            $table->index(['status']);
        });
    }
    public function down(): void { Schema::dropIfExists('coin_transactions'); }
};
