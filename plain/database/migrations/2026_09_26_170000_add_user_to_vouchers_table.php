<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            if (! Schema::hasColumn('vouchers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('vouchers', 'is_personal')) {
                $table->boolean('is_personal')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('vouchers', 'auto_type')) {
                $table->string('auto_type', 30)->nullable()->after('is_personal');
            }
            if (! Schema::hasColumn('vouchers', 'auto_year')) {
                $table->unsignedSmallInteger('auto_year')->nullable()->after('auto_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['is_personal', 'auto_type', 'auto_year']);
        });
    }
};