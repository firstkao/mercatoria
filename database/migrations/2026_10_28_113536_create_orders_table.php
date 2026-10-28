<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('no_order', 16)->unique();
            $table->string('status', 32)->default('menunggu_pembayaran');
            $table->string('scheme', 8);
            $table->string('platform', 16);
            $table->string('payment_method', 20);
            $table->decimal('subtotal', 14, 2);
            $table->string('discount_type', 10)->nullable();
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->unsignedBigInteger('coin_spent')->default(0);
            // Tanpa FK: orders ↔ user_vouchers saling merujuk (sirkular).
            $table->unsignedBigInteger('user_voucher_id')->nullable()->index();
            $table->decimal('total', 14, 2);
            $table->decimal('fee_qris', 14, 2)->default(0);
            $table->decimal('tagihan_web', 14, 2);
            $table->decimal('estimasi_co_marketplace', 14, 2)->nullable();
            $table->decimal('fee_co_marketplace', 14, 2)->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('hangus_at')->nullable();
            $table->timestamp('pickup_deadline_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
