<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    /**
     * Charset suffix: A-Z minus I/O + 0-9 minus 0/1.
     * Total 32 karakter → 32^5 = 33.554.432 kombinasi per nomor.
     * Exclude I/O/0/1 supaya gak ambiguous kalau dibaca manual
     * (mis. lewat telepon atau WhatsApp).
     */
    private const SUFFIX_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const SUFFIX_LENGTH = 5;
    private const SEQUENCE_PAD = 4;

    /**
     * Generate order number dalam format MER-YYYYMM-NNNN-XXXXX.
     * Contoh: MER-2610-0047-K3A7X
     *
     * ⚠️ HARUS dipanggil di dalam DB::transaction supaya:
     *   (a) lockForUpdate di order_sequences efektif
     *   (b) kalau outer transaction rollback, increment counter ikut rollback
     *       sehingga nomor berikutnya tidak lompat sia-sia.
     */
    public static function generate(): string
    {
        $yearMonth = now()->format('ym'); // "2610"

        $sequence = self::nextSequence($yearMonth);
        $suffix = self::randomSuffix();

        return sprintf(
            'MER-%s-%s-%s',
            $yearMonth,
            str_pad((string) $sequence, self::SEQUENCE_PAD, '0', STR_PAD_LEFT),
            $suffix
        );
    }

    /**
     * Ambil nomor urutan berikutnya untuk bulan tertentu, secara atomik.
     */
    private static function nextSequence(string $yearMonth): int
    {
        // Pastikan baris untuk bulan ini ada. Pakai firstOrCreate agar tidak
        // race condition pada baris yang belum ada (dua request paralel di
        // bulan baru sama-sama insert -> unique constraint violation).
        DB::table('order_sequences')->updateOrInsert(
            ['year_month' => $yearMonth],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $row = DB::table('order_sequences')
            ->where('year_month', $yearMonth)
            ->lockForUpdate()
            ->first();

        $next = ((int) $row->last_number) + 1;

        DB::table('order_sequences')
            ->where('year_month', $yearMonth)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }

    private static function randomSuffix(): string
    {
        $chars = self::SUFFIX_CHARSET;
        $max = strlen($chars) - 1;
        $out = '';
        for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
            $out .= $chars[random_int(0, $max)];
        }
        return $out;
    }
}
