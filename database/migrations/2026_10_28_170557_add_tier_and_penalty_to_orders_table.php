<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // PRD 50: hitungan tier idempotent
            $table->timestamp('tier_counted_at')->nullable()->after('cancel_reason');
            $table->decimal('tier_counted_amount', 14, 2)->nullable()->after('tier_counted_at');

            // PRD 51: denda keterlambatan CO (nilai dibekukan)
            $table->decimal('penalty_amount', 14, 2)->nullable()->after('tier_counted_amount');
            $table->timestamp('penalty_deadline_at')->nullable()->after('penalty_amount');
            $table->timestamp('penalty_paid_at')->nullable()->after('penalty_deadline_at');
            $table->timestamp('penalty_unlocked_at')->nullable()->after('penalty_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tier_counted_at',
                'tier_counted_amount',
                'penalty_amount',
                'penalty_deadline_at',
                'penalty_paid_at',
                'penalty_unlocked_at',
            ]);
        });
    }
};
