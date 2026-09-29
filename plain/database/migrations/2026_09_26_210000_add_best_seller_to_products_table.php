<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'best_seller_score')) {
                $table->unsignedInteger('best_seller_score')->default(0)->after('is_featured');
            }
            if (! Schema::hasColumn('products', 'best_seller_rank')) {
                $table->unsignedSmallInteger('best_seller_rank')->nullable()->after('best_seller_score');
            }
            if (! Schema::hasColumn('products', 'exclude_best_seller')) {
                $table->boolean('exclude_best_seller')->default(false)->after('best_seller_rank');
            }
        });

        // Index untuk query cepat
        Schema::table('products', function (Blueprint $table): void {
            $table->index(['best_seller_rank', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['best_seller_rank', 'is_published']);
            $table->dropColumn(['best_seller_score', 'best_seller_rank', 'exclude_best_seller']);
        });
    }
};