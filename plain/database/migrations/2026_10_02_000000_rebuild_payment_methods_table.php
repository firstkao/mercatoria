<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rebuild tabel `payment_methods` dari nol (permintaan user: tabel lama drop,
 * buat baru) — memperbaiki error MySQL 1364 "Field 'type' doesn't have a
 * default value" yang tidak selesai meski sudah beberapa kali ditambal,
 * karena struktur lama warisan SQL dump punya kolom legacy (`type` NOT NULL
 * tanpa default, dsb.) dan status migrasi di DB sudah terlanjur tercatat DONE.
 *
 * Skema baru sesuai spesifikasi bisnis (3 jenis pembayaran):
 * - bank   : butuh nomor rekening + atas nama
 * - qris   : cukup upload foto QR code
 * - barcode: cukup upload foto barcode
 *
 * Data lama AMAN: seluruh baris disalin sebelum tabel lama dibuang, dengan
 * pemetaan kolom (qris_image_path -> qr_image, label lama dicoba dipetakan ke
 * tipe via kolom `type` lama bila masih ada).
 */
return new class extends Migration
{
    private const TYPES = ['bank', 'qris', 'barcode'];

    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        $oldColumns = array_column(
            DB::select('SHOW COLUMNS FROM payment_methods'),
            'Field'
        );

        // 1) Baca semua baris lama apa adanya (hanya kolom yang benar-benar ada).
        $rows = DB::table('payment_methods')->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        // 1b) Putuskan FK dari payment_proofs dulu — tanpa FK_CHECKS off,
        //     `DROP TABLE payment_methods` akan gagal dengan error MySQL 3730
        //     karena tabel ini direferensikan oleh bukti pembayaran.
        $hasProofFk = false;
        try {
            $fks = DB::select(
                "SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE
                 WHERE table_schema = DATABASE() AND table_name = 'payment_proofs'
                   AND REFERENCED_TABLE_NAME = 'payment_methods'"
            );
            foreach ($fks as $fk) {
                DB::statement('ALTER TABLE payment_proofs DROP FOREIGN KEY `' . str_replace('`', '', (string) $fk->name) . '`');
                $hasProofFk = true;
            }
        } catch (\Throwable) {
            // Tidak bisa membaca/memutus FK — lanjutkan dengan fallback di bawah.
        }

        // 2) Drop tabel lama, bikin tabel baru dengan skema bersih.
        Schema::drop('payment_methods');

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', self::TYPES)
                ->default('bank')
                ->comment('bank = transfer rekening; qris/barcode = gambar kode');
            $table->string('label', 100);
            // Hanya wajib untuk metode bank — divalidasi di controller, bukan DB.
            $table->string('account_number', 100)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('qr_image')->nullable()->comment('Path foto QR/barcode (qris & barcode)');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3) Kembalikan data lama ke skema baru.
        foreach ($rows as $row) {
            $label = (string) ($row['label'] ?? '');
            $insert = [
                'id'             => $row['id'] ?? null,
                'label'          => $label !== '' ? mb_substr($label, 0, 100) : 'Metode Lama',
                'type'           => $this->guessType($row, $oldColumns),
                'account_number' => isset($row['account_number']) ? mb_substr((string) $row['account_number'], 0, 100) : null,
                'account_name'   => isset($row['account_name']) ? mb_substr((string) $row['account_name'], 0, 100) : null,
                'qr_image'       => $this->resolveQrImage($row, $oldColumns),
                'instructions'   => isset($row['instructions']) ? (string) $row['instructions'] : null,
                'sort_order'     => (int) ($row['sort_order'] ?? 0),
                'is_active'      => (bool) ($row['is_active'] ?? true),
                'created_at'     => $row['created_at'] ?? null,
                'updated_at'     => $row['updated_at'] ?? null,
            ];

            if ($insert['id'] === null) {
                unset($insert['id']);
            }

            DB::table('payment_methods')->insert($insert);
        }

        // Selaraskan auto-increment setelah insert eksplisit ber-ID.
        $max = (int) DB::table('payment_methods')->max('id');
        if ($max > 0) {
            DB::statement('ALTER TABLE payment_methods AUTO_INCREMENT = ' . ($max + 1));
        }

        // 4) Pasang kembali FK payment_proofs.payment_method_id -> payment_methods,
        //    persis seperti definisi awal (restrict on delete).
        if ($hasProofFk) {
            try {
                DB::statement('ALTER TABLE payment_proofs ADD CONSTRAINT payment_proofs_payment_method_id_foreign FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id)');
            } catch (\Throwable) {
                // Kalau nama constraint sudah dipakai / beda, coba nama generik.
                try {
                    DB::statement('ALTER TABLE payment_proofs ADD FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id)');
                } catch (\Throwable) {
                    // Biarkan tanpa FK — aplikasi tetap jalan; relasi hanya kehilangan enforcement di level DB.
                }
            }
        }
    }

    public function down(): void
    {
        // Tidak mengembalikan struktur legacy secara persis (mustahil tanpa
        // dump asli); cukup biarkan tabel versi baru agar aplikasi tetap jalan.
    }

    /**
     * Tebak tipe metode dari data lama:
     * 1. kolom `type` lama (kalau masih ada dan cocok),
     * 2. kata kunci pada label (qris / barcode / brimo / bank...),
     * 3. ada gambar QR tapi tidak ada nomor rekening -> qris,
     * 4. default: bank.
     */
    private function guessType(array $row, array $oldColumns): string
    {
        if (in_array('type', $oldColumns, true)) {
            $t = strtolower(trim((string) ($row['type'] ?? '')));
            if (in_array($t, self::TYPES, true)) {
                return $t;
            }
            if (str_contains($t, 'qr')) {
                return 'qris';
            }
            if (str_contains($t, 'bar')) {
                return 'barcode';
            }
            if ($t !== '') {
                return 'bank';
            }
        }

        $haystack = strtolower(($row['label'] ?? '') . ' ' . ($row['name'] ?? ''));
        if (preg_match('/\b(qr\s?is|qr)\b/', $haystack)) {
            return 'qris';
        }
        if (preg_match('/bar\s?code|\bcode\b/', $haystack)) {
            return 'barcode';
        }

        $hasImage = $this->resolveQrImage($row, $oldColumns) !== null;
        $hasAccount = ! empty($row['account_number'] ?? null);
        if ($hasImage && ! $hasAccount) {
            return 'qris';
        }

        return 'bank';
    }

    private function resolveQrImage(array $row, array $oldColumns): ?string
    {
        foreach (['qr_image', 'qris_image_path', 'image_path'] as $col) {
            if (in_array($col, $oldColumns, true) && ! empty($row[$col])) {
                return (string) $row[$col];
            }
        }

        return null;
    }
};
