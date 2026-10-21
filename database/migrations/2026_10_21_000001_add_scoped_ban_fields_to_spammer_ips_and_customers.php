<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spammer_ips', function (Blueprint $table) {
            $table->string('device_fingerprint', 128)->nullable()->after('ip_address');
            $table->timestamp('expires_at')->nullable()->after('last_attempt_at');
            $table->index(['ip_address', 'expires_at']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedTinyInteger('unblock_count')->default(0)->after('current_state');
            $table->timestamp('unblock_grace_until')->nullable()->after('unblock_count');
        });

        Schema::table('email_logs', function (Blueprint $table) {
            $table->string('status', 32)->change();
        });
    }

    public function down(): void
    {
        Schema::table('spammer_ips', function (Blueprint $table) {
            $table->dropIndex(['ip_address', 'expires_at']);
            $table->dropColumn(['device_fingerprint', 'expires_at']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['unblock_count', 'unblock_grace_until']);
        });
    }
};
