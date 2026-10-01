<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Bugfix ronde 2 #2 (error 500 saat simpan metode pembayaran):
     * DB produksi dibuat dari SQL dump lama yang strukturnya beda — punya kolom
     * `type` NOT NULL tanpa default dan `qris_image_path`, TIDAK punya
     * `instructions`/`qr_image`. Migration lama memakai ->after('instructions')
     * yang gagal di MySQL karena kolom itu tidak ada, sehingga `php artisan
     * migrate` selalu error dan qr_image tak pernah tercipta. Sekarang:
     * - kolom ditambahkan tanpa ->after() (aman di semua struktur),
     * - kolom dump lama (`type`, dsb.) diberi default agar insert tidak 500,
     * - isi qris_image_path dipindah ke qr_image lalu kolom lama dihapus,
     * - migration idempoten (aman dijalankan berkali-kali).
     */
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        // 1) Kolom baru yang dibutuhkan aplikasi, nullable/tanpa default ketat.
        Schema::table('payment_methods', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_methods', 'instructions')) {
                $table->text('instructions')->nullable();
            }
            if (! Schema::hasColumn('payment_methods', 'qr_image')) {
                $table->string('qr_image')->nullable();
            }
        });

        // 2) Kolom warisan dump lama yang NOT NULL tanpa default ('type') akan
        //    melempar error MySQL 1364 saat INSERT dari admin. Cara lama:
        //    `MODIFY type VARCHAR(10) NOT NULL DEFAULT ''` — tapi ini GAGAL di
        //    sebagian server (kolom aslinya enum, atau MySQL/MariaDB lama
        //    menolak DEFAULT pada kolom teks/enum), sehingga migration tercatat
        //    DONE padahal langkah ini tidak pernah berhasil. Sekarang: jadikan
        //    NULL-able saja (paling kompatibel), karena aplikasi tidak pernah
        //    membaca/menulis kolom 'type'.
        if (Schema::hasColumn('payment_methods', 'type')) {
            try {
                DB::statement('ALTER TABLE payment_methods MODIFY type VARCHAR(40) NULL');
            } catch (\Throwable) {
                // Fallback: minimal buat nullable tanpa mengubah tipe.
                try {
                    DB::statement('ALTER TABLE payment_methods MODIFY type ENUM(\'bank\',\'qris\',\'ewallet\',\'other\') NULL');
                } catch (\Throwable $e) {
                    Log::warning('Gagal menormalkan kolom payment_methods.type: ' . $e->getMessage());
                }
            }
        }

        // 3) Migrasi data: qris_image_path -> qr_image, lalu buang kolom lama.
        if (Schema::hasColumn('payment_methods', 'qris_image_path')) {
            DB::table('payment_methods')
                ->whereNull('qr_image')
                ->whereNotNull('qris_image_path')
                ->update(['qr_image' => DB::raw('qris_image_path')]);

            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('qris_image_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'qr_image')) {
                $table->dropColumn('qr_image');
            }
        });
    }
};
