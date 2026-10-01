<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 2 — verifikasi otomatis pembayaran QRIS.
 *
 * Menambah kolom `auto_verify` pada payment_methods. Hanya metode bertipe
 * 'qris' yang boleh mengaktifkannya: saat user mengunggah bukti bayar dengan
 * metode tersebut, aplikasi memindai gambar dan mencocokkan NMI Merchant Account
 * Information (tag 8704 subtag 00) / merchant ID (tag 54) dengan nilai yang
 * diisikan admin (`account_number`). Cocok -> status 'approved' + order lanjut
 * ke 'pembayaran_diterima' tanpa review manual. Tidak cocok/error -> tetap
 * 'pending' (masuk antrean review admin seperti biasa).
 *
 * Idempoten & aman untuk tabel hasil rebuild (2026_10_02) maupun skema lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        if (! Schema::hasColumn('payment_methods', 'auto_verify')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->boolean('auto_verify')->default(false)
                    ->after('is_active')
                    ->comment('Fase 2: QRIS terverifikasi otomatis bila NMI/merchant id cocok');
            });
        }

        // Safety net: pastikan `type` punya default agar MySQL 1364 tidak bisa
        // terjadi lagi walau tabel dibuat dari SQL dump lama tanpa DEFAULT.
        try {
            $col = collect(DB::select("SHOW COLUMNS FROM payment_methods LIKE 'type'"))->first();
            if ($col !== null && $col->Default === null && str_contains((string) $col->Null, 'NO')) {
                DB::statement("ALTER TABLE payment_methods MODIFY type ENUM('bank','qris','barcode') NOT NULL DEFAULT 'bank'");
            }
        } catch (\Throwable) {
            // Bukan MySQL / struktur tak dikenal — lewati, controller tetap guard.
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'auto_verify')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('auto_verify');
            });
        }
    }
};
