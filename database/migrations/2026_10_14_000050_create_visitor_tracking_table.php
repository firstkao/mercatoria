<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visitor_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique();
            $table->string('referral_source')->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('first_visit_at')->nullable();
            $table->timestamps();
            $table->index(['referral_source']);
        });
    }
    public function down(): void { Schema::dropIfExists('visitor_tracking'); }
};
