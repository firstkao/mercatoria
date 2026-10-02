<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalisasi nilai kolom `payment_methods.type` ke enum modern: bank|qris|barcode.
 *
 * Kenapa perlu: payment_proofs.payment_method_id memakai FK RESTRICT default.
 * Kalau masih ada baris payment_methods dengan type warisan dump lama
 * (mis. 'transfer', 'cod', 'manual') yang TIDAK termasuk dalam list enum baru,
 * maka setiap upload bukti pembayaran akan gagal dengan SQLSTATE[23000]
 * (check constraint MySQL 8 / "Data truncated for column 'type'") —
 * karena MySQL harus menandai baris parent sebagai tidak valid saat insert child.
 *
 * Migration ini memetakan nilai lama -> tipe baru secara heuristik:
 *   - punya qr_image/gambar & label mengandung qris/qr/barcode -> qris/barcode
 *   - selain itu -> bank (default paling aman untuk store transfer manual)
 * Idempotent: hanya menyentuh baris yang nilainya di luar enum baru.
 */
return new class extends Migration
{
    private const VALID = ['bank', 'qris', 'barcode'];

    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasColumn('payment_methods', 'type')) {
            return;
        }

        $rows = DB::table('payment_methods')->select('id', 'type', 'label', 'qr_image')->get();

        foreach ($rows as $row) {
            if (in_array((string) $row->type, self::VALID, true)) {
                continue; // sudah valid, lewati
            }

            $haystack = strtolower(implode(' ', array_filter([
                (string) $row->type,
                (string) ($row->label ?? ''),
            ])));

            $new = 'bank';
            if (str_contains($haystack, 'barcode')) {
                $new = 'barcode';
            } elseif (str_contains($haystack, 'qris') || str_contains($haystack, 'qr')) {
                $new = 'qris';
            } elseif (! empty($row->qr_image)) {
                $new = 'qris'; // punya gambar QR tanpa kata kunci -> asumsikan QRIS
            }

            DB::table('payment_methods')->where('id', $row->id)->update(['type' => $new]);
        }
    }

    public function down(): void
    {
        // Non-destruktif: nilai lama tidak disimpan, jadi tidak bisa dipulihkan.
    }
};
