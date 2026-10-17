<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('share_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('country_code', 2)->nullable();
            $table->boolean('reward_given')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'product_id', 'ip_address'], 'share_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('share_clicks'); }
};
