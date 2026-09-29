<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable();
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable();
            }
            if (! Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable();
            }
            if (! Schema::hasColumn('users', 'street_address')) {
                $table->text('street_address')->nullable();
            }
            if (! Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp', 20)->nullable()->unique();
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('spammer');
            }
            if (! Schema::hasColumn('users', 'view_quota_used')) {
                $table->unsignedSmallInteger('view_quota_used')->default(0);
            }
            if (! Schema::hasColumn('users', 'parental_consent')) {
                $table->boolean('parental_consent')->default(false);
            }
            if (! Schema::hasColumn('users', 'tnc_accepted_at')) {
                $table->timestamp('tnc_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'privacy_accepted_at')) {
                $table->timestamp('privacy_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'registered_at')) {
                $table->timestamp('registered_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'became_customer_at')) {
                $table->timestamp('became_customer_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'anonymized_at')) {
                $table->timestamp('anonymized_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'full_name', 'birth_date', 'province', 'city', 'district', 'postal_code',
                'street_address', 'whatsapp', 'role', 'view_quota_used', 'parental_consent',
                'tnc_accepted_at', 'privacy_accepted_at', 'registered_at', 'expires_at',
                'became_customer_at', 'anonymized_at',
            ]);
        });
    }
};