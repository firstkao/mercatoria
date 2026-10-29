<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // PRD 44: Note to Customer (admin → customer, 1-way, max 1000).
            $table->text('note_to_customer')->nullable()->after('customer_note');
            $table->timestamp('note_to_customer_updated_at')->nullable()->after('note_to_customer');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['note_to_customer', 'note_to_customer_updated_at']);
        });
    }
};
