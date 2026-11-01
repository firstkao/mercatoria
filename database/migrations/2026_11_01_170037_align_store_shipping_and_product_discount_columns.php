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
        Schema::table('stores', function (Blueprint $table): void {
            $table->renameColumn('ongkir_lokal_cn_percent', 'ongkir_rmb');
        });

        Schema::table('stores', function (Blueprint $table): void {
            $table->decimal('ongkir_rmb', 10, 2)->default(0)->change();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->renameColumn('discount_amount_rmb', 'discount_amount');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('discount_amount', 14, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('discount_amount', 12, 2)->nullable()->change();
            $table->renameColumn('discount_amount', 'discount_amount_rmb');
        });

        Schema::table('stores', function (Blueprint $table): void {
            $table->decimal('ongkir_rmb', 5, 2)->default(7)->change();
            $table->renameColumn('ongkir_rmb', 'ongkir_lokal_cn_percent');
        });
    }
};
