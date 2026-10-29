<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // PRD 23: nomor invoice INV-MER-{4 digit}, sinkron sequence no_order.
            $table->string('invoice_number', 20)->nullable()->unique()->after('no_order');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};
