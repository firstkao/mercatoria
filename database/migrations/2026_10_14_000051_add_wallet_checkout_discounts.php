<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('coin_discount_amount', 14, 2)->default(0)->after('discount_amount');
            $table->decimal('voucher_discount_amount', 14, 2)->default(0)->after('coin_discount_amount');
            $table->foreignId('user_voucher_id')->nullable()
                ->after('voucher_discount_amount')
                ->constrained('user_vouchers')->nullOnDelete();
        });

        Schema::table('coin_transactions', function (Blueprint $table): void {
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coin_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_voucher_id');
            $table->dropColumn(['coin_discount_amount', 'voucher_discount_amount']);
        });
    }
};
