<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) return;

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained('marketplaces')->nullOnDelete();
            $table->string('status', 30)->default('menunggu_pembayaran');
            $table->string('payment_scheme', 5);
            $table->unsignedInteger('subtotal_idr');
            $table->string('discount_type', 20)->default('none');
            $table->unsignedInteger('discount_idr')->default(0);
            $table->unsignedInteger('total_idr');
            $table->unsignedInteger('pay_now_idr');
            $table->unsignedInteger('remaining_idr')->default(0);
            $table->unsignedInteger('marketplace_fee_idr')->default(0);
            $table->unsignedInteger('coin_estimate')->default(0);
            $table->json('pricing_snapshot')->nullable();
            $table->timestamp('payment_deadline_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('refund_note')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};