<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('appeals')) {
            Schema::create('appeals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('identity_record_id')->nullable()->constrained('identity_records')->nullOnDelete();
                $table->string('type', 40);
                $table->text('reason');
                $table->string('status', 20)->default('pending');
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->text('admin_note')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_addresses')) {
            Schema::create('order_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('recipient_name');
                $table->string('whatsapp', 20)->nullable();
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->string('district')->nullable();
                $table->string('postal_code', 10)->nullable();
                $table->text('street_address')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payment_proofs') && ! Schema::hasColumn('payment_proofs', 'payment_stage')) {
            Schema::table('payment_proofs', function (Blueprint $table): void {
                $table->string('payment_stage', 10)->default('dp')->after('payment_method_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_proofs') && Schema::hasColumn('payment_proofs', 'payment_stage')) {
            Schema::table('payment_proofs', function (Blueprint $table): void {
                $table->dropColumn('payment_stage');
            });
        }

        if (Schema::hasTable('order_addresses')) {
            Schema::dropIfExists('order_addresses');
        }

        if (Schema::hasTable('appeals')) {
            Schema::dropIfExists('appeals');
        }
    }
};
