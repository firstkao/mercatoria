<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GATEKEEPER #2 — tabel daftar hitam IP.
 *
 * Struktur dibuat TOLERAN: kalau ternyata hosting sudah punya tabel
 * `blacklisted_ips` dengan kolom berbeda (mis. dari dump lama bernama
 * `ip`), migration ini tidak merusak apa pun — hanya menambah yang kurang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blacklisted_ips')) {
            Schema::create('blacklisted_ips', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->unique();
                $table->string('reason')->nullable();
                $table->timestamp('expires_at')->nullable(); // null = permanen
                $table->timestamps();
            });

            return;
        }

        // Tabel sudah ada tapi kolomnya beda? Tambah pelan-pelan, jangan drop.
        Schema::table('blacklisted_ips', function (Blueprint $table) {
            if (!Schema::hasColumn('blacklisted_ips', 'ip_address')
                && Schema::hasColumn('blacklisted_ips', 'ip')) {
                $table->string('ip_address', 45)->nullable()->after('id');
            }
            if (!Schema::hasColumn('blacklisted_ips', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });

        // Migrasikan data dari kolom lama `ip` → `ip_address`.
        if (Schema::hasColumn('blacklisted_ips', 'ip')
            && Schema::hasColumn('blacklisted_ips', 'ip_address')) {
            DB::table('blacklisted_ips')
                ->whereNull('ip_address')
                ->whereNotNull('ip')
                ->update(['ip_address' => DB::raw('ip')]);
        }
    }

    public function down(): void
    {
        // Toleran terhadap tabel yang sudah ada sebelum migrasi ini: kalau tabel
        // punya kolom `reason` (hanya dibuat oleh jalur CREATE di atas), berarti
        // tabel itu milik migrasi ini -> aman di-drop. Kalau tidak (tabel legacy),
        // cukup buang kolom yang kita tambahkan, JANGAN drop tabelnya.
        if (! Schema::hasTable('blacklisted_ips')) {
            return;
        }

        if (Schema::hasColumn('blacklisted_ips', 'reason')) {
            Schema::dropIfExists('blacklisted_ips');

            return;
        }

        Schema::table('blacklisted_ips', function (Blueprint $table) {
            if (Schema::hasColumn('blacklisted_ips', 'ip_address') && Schema::hasColumn('blacklisted_ips', 'ip')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('blacklisted_ips', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
