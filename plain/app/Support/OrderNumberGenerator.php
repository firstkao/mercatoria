<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    /**
     * Charset suffix: A-Z minus I/O + 0-9 minus 0/1.
     * Total 32 karakter → 32^5 = 33.554.432 kombinasi per nomor.
     * Exclude I/O/0/1 supaya gak ambiguous kalau dibaca manual
     * (lewat telepon / WhatsApp).
     */
    private const SUFFIX_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const SUFFIX_LENGTH = 5;
    private const SEQUENCE_PAD = 4;

    /**
     * Format: MER-{XXXXX}-{NNNN}
     *   XXXXX : 5 char random (anti-enumeration)
     *   NNNN  : urutan global, counter terus jalan (tidak reset per bulan)
     *
     * Contoh: MER-A7K3P-0047
     *
     * ⚠️ Wajib dipanggil DI DALAM DB::transaction supaya:
     *   1. lockForUpdate di order_sequences efektif
     *   2. kalau outer transaction rollback, increment counter ikut rollback
     *      sehingga nomor berikutnya tidak lompat sia-sia.
     */
    public static function generate(): string
    {
        $sequence = self::nextSequence();
        $suffix = self::randomSuffix();

        return sprintf(
            'MER-%s-%s',
            $suffix,
            str_pad((string) $sequence, self::SEQUENCE_PAD, '0', STR_PAD_LEFT)
        );
    }

    /**
     * Ambil nomor urutan berikutnya, atomik. Counter global (satu baris).
     */
    private static function nextSequence(): int
    {
        // Pastikan baris tunggal (id=1) ada
        DB::table('order_sequences')->updateOrInsert(
            ['id' => 1],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $row = DB::table('order_sequences')
            ->where('id', 1)
            ->lockForUpdate()
            ->first();

        $next = ((int) $row->last_number) + 1;

        DB::table('order_sequences')
            ->where('id', 1)
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
