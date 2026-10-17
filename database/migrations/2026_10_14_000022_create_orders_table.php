<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 32)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('menunggu_pembayaran');
            $table->string('payment_scheme', 8);
            $table->string('platform', 16);
            $table->string('payment_method', 32);
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            $table->text('customer_address');
            $table->string('referral_source')->nullable();
            $table->string('landing_page')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('note_to_customer')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('note_to_customer_updated_at')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('fee_marketplace', 14, 2)->default(0);
            $table->decimal('fee_qris', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('frozen_rate_rmb_idr', 10, 2)->nullable();
            $table->decimal('frozen_profit_percent', 5, 2)->nullable();
            $table->timestamp('pickup_deadline_at')->nullable();
            $table->timestamp('hangus_at')->nullable();
            $table->timestamps();
            $table->index(['status']);
            $table->index(['user_id', 'status']);
            $table->index(['created_at']);
            $table->index(['pickup_deadline_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
