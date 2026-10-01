<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->date('clicked_on'); // untuk anti-spam: 1 klik dihitung per IP per hari
            $table->timestamps();

            $table->unique(['referrer_id', 'ip_address', 'clicked_on'], 'referral_clicks_unique_daily');
            $table->index(['referrer_id', 'clicked_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};
