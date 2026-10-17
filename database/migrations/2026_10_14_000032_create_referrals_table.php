<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('referee_registered_at')->nullable();
            $table->timestamp('referee_first_paid_at')->nullable();
            $table->timestamp('referee_first_order_completed_at')->nullable();
            $table->integer('referee_coin_given')->default(0);
            $table->integer('referrer_coin_given')->default(0);
            $table->timestamps();
            $table->unique(['referrer_id', 'referee_id'], 'referral_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('referrals'); }
};
