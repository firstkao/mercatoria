<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('no_hp', 20)->unique()->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('role', 32)->default('customer');
            $table->string('referral_code', 16)->unique()->nullable();
            $table->string('invited_by_code', 16)->nullable();
            $table->boolean('has_seen_edu_modal')->default(false);
            $table->boolean('has_seen_tour')->default(false);
            $table->boolean('is_migrated')->default(false);  // BARU v1.5
            $table->timestamp('last_login')->nullable();
            $table->string('admin_otp', 6)->nullable();
            $table->timestamp('admin_otp_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->index(['role']);
            $table->index(['last_login']);
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
