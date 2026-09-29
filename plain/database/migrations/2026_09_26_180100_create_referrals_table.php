<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 20);
            $table->string('status', 20)->default('pending'); // pending, rewarded, cancelled
            $table->unsignedInteger('referrer_reward')->default(0);
            $table->unsignedInteger('referee_reward')->default(0);
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->unique('referee_id'); // satu referee = satu referral
            $table->index(['referrer_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};