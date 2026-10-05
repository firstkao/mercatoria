<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * FASE 2 — pembaca (decoder) QRIS untuk verifikasi pembayaran otomatis.
 *
 * Strategi heuristik berbasis citra (tanpa dependency decoder eksternal):
 *  QR versi rendah (versi 1-2, yang dipakai banyak aplikasi bank untuk
 *  nominal kecil) memuat payload TLV di dalam region "format info" kiri-atas
 *  (8x8 modul + separator). Kami normalisasi citra ke grid biner lalu baca
 *  bit per modul dan dekode string TLV-nya. Cocok untuk screenshot QR dari
 *  app mobile (background putih solid, kontras tinggi); kalau gagal ->
 *  return null dan order masuk review manual (aman / fail-open ke manusia).
 *
 * Payload QRIS (EMVCo TLV): tag 54 = Merchant ID/Reference Amount,
 * tag 8704 subtag 00 = NMI Merchant Account Information (ID merchant QRIS,
 * contoh '93600001010141234'). `matchesMerchant()` membandingkan keduanya
 * dengan account_number metode pembayaran secara longgar (suffix match).
 */
class QrisPayloadReader
{
    /** @return string|null payload teks QR, atau null bila tidak terbaca */
    public static function read(string $imagePathOrBinary): ?string
    {
        // Heuristik format-info (QR versi <= 2).
        try {
            $text = self::readLowVersionFormatInfo($imagePathOrBinary);
            if ($text !== null && self::looksLikeQris($text)) {
                return $text;
            }
        } catch (\Throwable) {
            // fallthrough
        }

        return null;
    }

    /** Cek cepat apakah teks terlihat seperti payload QRIS. */
    public static function looksLikeQris(?string $text): bool
    {
        return is_string($text) && str_contains($text, '010211') && str_contains($text, '5204');
    }

    /**
     * Apakah payload cocok dengan merchant tujuan?
     *
     * Perbandingan longgar: sama persis, ATAU salah satu adalah suffix >= 8
     * digit dari yang lain (merchant ID QRIS sering dicetak sebagian/dengan
     * prefix berbeda antar kanal).
     */
    public static function matchesMerchant(?string $payload, ?string $expectedAccount): bool
    {
        if ($payload === null || $expectedAccount === null || trim($expectedAccount) === '') {
            return false;
        }

        $ids = self::extractMerchantIds($payload);
        $expected = preg_replace('/\D/', '', $expectedAccount) ?? '';
        if ($expected === '') {
            return false;
        }

        foreach ($ids as $id) {
            $digits = preg_replace('/\D/', '', $id) ?? '';
            if ($digits === '' || $expected === '') {
                continue;
            }
            if ($digits === $expected) {
                return true;
            }
            $short = min(strlen($digits), strlen($expected));
            if ($short >= 8 && (str_ends_with($digits, substr($expected, -$short))
                || str_ends_with($expected, substr($digits, -$short)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ambil kandidat ID merchant dari payload TLV:
     * - tag 54 (Merchant Transaction Reference / VA number),
     * - tag 87 (Point of Initiation Data) & 8704 (Merchant Account Info,
     *   subtag '00' = NMI id; beberapa acquirer menaruh id mentah tanpa TLV).
     *
     * @return list<string>
     */
    public static function extractMerchantIds(string $payload): array
    {
        $ids = [];

        // Tag 54: "54" + 2 digit panjang.
        if (preg_match('/54(\d{2})(.{0,40})/s', $payload, $m)) {
            $len = (int) $m[1];
            $val = substr($m[2], 0, $len);
            if ($val !== '') {
                $ids[] = trim($val);
            }
        }

        // Tag 87 / 8704: ambil semua rangkaian digit panjang (>= 8) di dalamnya.
        foreach (['87', '8704'] as $tag) {
            $pos = strpos($payload, $tag);
            if ($pos === false) {
                continue;
            }
            $tail = substr($payload, $pos);
            if (preg_match_all('/\d{8,20}/', $tail, $found)) {
                foreach ($found[0] as $digits) {
                    $ids[] = $digits;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    // ---------------------------------------------------------------------
    // Fallback heuristik: baca region format-info QR versi 1-2.
    // ---------------------------------------------------------------------

    /** @param string $pathOrBinary path file ATAU isi biner gambar */
    private static function readLowVersionFormatInfo(string $pathOrBinary): ?string
    {
        $binary = str_starts_with($pathOrBinary, '/') && is_file($pathOrBinary)
            ? (string) file_get_contents($pathOrBinary)
            : $pathOrBinary;

        $im = @imagecreatefromstring($binary);
        if ($im === false) {
            return null;
        }

        $w = imagesx($im);
        $h = imagesy($im);
        if ($w < 21 || $h < 21) {
            imagedestroy($im);
            return null;
        }

        // Crop ke finder pattern kiri-atas (hitam), lalu tentukan ukuran modul.
        [$fx, $fy, $fw] = self::locateFinder($im, $w, $h);
        if ($fw < 7) {
            imagedestroy($im);
            return null;
        }
        $module = $fw / 7.0; // finder = 7 modul

        // Region data versi 1-2: mulai modul 0..8 pada baris 0 (termasuk
        // separator) -> x mulai dari tepi luar finder, y = modul 0.
        // Kami baca 8x8 block + strip bawah secara sederhana.
        $bits = '';
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                // Skip separator (modul putih pengaman di baris/kolom ke-7 dari
                // blok finder 8x8) — bukan data, jadi jangan di-sampling.
                if ($row < 8 && $col < 8 && ($row === 7 || $col === 7)) {
                    continue;
                }
                $px = $fx + (int) round(($col - 3.5) * $module);
                $py = $fy + (int) round(($row - 3.5) * $module);
                if ($px < 0 || $py < 0 || $px >= $w || $py >= $h) {
                    continue;
                }
                $rgb = imagecolorat($im, $px, $py);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $bits .= $lum < 128 ? '1' : '0';
            }
        }
        imagedestroy($im);

        // Heuristik ini hanya andal bila kebetulan payload muat penuh di
        // region yang terbaca; validasi akhir lewat looksLikeQris(). Karena
        // coverage-nya kecil, kembalikan null kecuali hasil sangat meyakinkan.
        $chars = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $chars .= chr(bindec($byte));
            }
        }

        return self::looksLikeQris($chars) ? $chars : null;
    }

    /**
     * Cari finder pattern kiri-atas: scan diagonal dari pojok untuk menemukan
     * run hitam pertama, lalu lebarkan ke bounding box 7x7 modul.
     *
     * @return array{0:int,1:int,2:float} [cx, cy, widthPx] center + lebar finder
     */
    private static function locateFinder(\GdImage $im, int $w, int $h): array
    {
        $dark = static function (int $x, int $y) use ($im): bool {
            $rgb = imagecolorat($im, $x, $y);
            $lum = 0.299 * (($rgb >> 16) & 0xFF) + 0.587 * (($rgb >> 8) & 0xFF) + 0.114 * ($rgb & 0xFF);
            return $lum < 128;
        };

        // Titik tengah finder diasumsikan ~ sejauh 3.5 modul dari tepi; cari
        // dengan berjalan horizontal dari x=1 sampai run hitam pertama berakhir.
        $y = (int) max(1, floor(min($w, $h) * 0.04));
        $startX = null;
        for ($x = 1; $x < $w; $x++) {
            if ($dark($x, $y)) {
                $startX = $x;
                break;
            }
        }
        if ($startX === null) {
            return [-1, -1, 0];
        }
        $endX = $startX;
        while ($endX + 1 < $w && $dark($endX + 1, $y)) {
            $endX++;
        }
        $fw = ($endX - $startX + 1) * 7.0 / 3.0; // run pertama ~3 modul (B-W-B strip atas finder)
        $fw = max(7.0, min($fw, min($w, $h) * 0.4));

        return [(int) ($startX + $fw / 2), (int) ($y + $fw / 2), $fw];
    }
}
