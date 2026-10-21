<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16);
            $table->integer('coin_amount')->nullable();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gifts');
    }
};
