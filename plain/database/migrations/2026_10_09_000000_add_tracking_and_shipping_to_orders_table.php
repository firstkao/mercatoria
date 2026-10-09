<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // === Shipping / penerima ===
            if (! Schema::hasColumn('orders', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('orders', 'recipient_phone')) {
                $table->string('recipient_phone', 30)->nullable()->after('recipient_name');
            }
            if (! Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('recipient_phone');
            }
            if (! Schema::hasColumn('orders', 'shipping_city')) {
                $table->string('shipping_city', 100)->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_postal_code')) {
                $table->string('shipping_postal_code', 10)->nullable()->after('shipping_city');
            }
            if (! Schema::hasColumn('orders', 'shipping_note')) {
                $table->string('shipping_note', 500)->nullable()->after('shipping_postal_code');
            }

            // === Tracking (opsional) ===
            if (! Schema::hasColumn('orders', 'source')) {
                $table->string('source', 50)->nullable();
            }
            if (! Schema::hasColumn('orders', 'device_type')) {
                $table->string('device_type', 20)->nullable();
            }
            if (! Schema::hasColumn('orders', 'landing_page')) {
                $table->string('landing_page', 500)->nullable();
            }
            if (! Schema::hasColumn('orders', 'referrer')) {
                $table->string('referrer', 500)->nullable();
            }
            if (! Schema::hasColumn('orders', 'session_page_views')) {
                $table->unsignedSmallInteger('session_page_views')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'recipient_name',
                'recipient_phone',
                'shipping_address',
                'shipping_city',
                'shipping_postal_code',
                'shipping_note',
                'source',
                'device_type',
                'landing_page',
                'referrer',
                'session_page_views',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
