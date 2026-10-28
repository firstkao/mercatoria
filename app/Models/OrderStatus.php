<?php

namespace App\Models;

/**
 * Status order (PRD 18) — 11 normal + 5 gagal/refund/denda.
 * Nilai snake_case mengikuti Skema DB §18 ("menunggu_denda", "hangus_permanen").
 */
enum OrderStatus: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';

    case Ditahan = 'ditahan';

    case PembayaranDiterima = 'pembayaran_diterima';

    case SedangDiproses = 'sedang_diproses';

    case SampaiWhChina = 'sampai_wh_china';

    case DikirimKeIndonesia = 'dikirim_ke_indonesia';

    case BeaCukai = 'bea_cukai';

    case SampaiWhIndonesia = 'sampai_wh_indonesia';

    case MenungguPelunasanPengambilan = 'menunggu_pelunasan_pengambilan';

    case SedangDikemas = 'sedang_dikemas';

    case Selesai = 'selesai';

    case PembayaranGagal = 'pembayaran_gagal';

    case DanaDikembalikan = 'dana_dikembalikan';

    case Hangus = 'hangus';

    case MenungguDenda = 'menunggu_denda';

    case HangusPermanen = 'hangus_permanen';

    /**
     * Transisi yang diizinkan (dari → daftar tujuan).
     *
     * Transisi denda (Hangus → Menunggu Denda → …) diaktifkan di bagian (h).
     *
     * @return array<string, array<int, string>>
     */
    public static function transitions(): array
    {
        return [
            self::MenungguPembayaran->value => [
                self::Ditahan->value,
                self::PembayaranGagal->value,
            ],
            self::Ditahan->value => [
                self::PembayaranDiterima->value,
                self::MenungguPembayaran->value,
                self::PembayaranGagal->value,
            ],
            self::PembayaranDiterima->value => [
                self::SedangDiproses->value,
                self::DanaDikembalikan->value,
            ],
            self::SedangDiproses->value => [
                self::SampaiWhChina->value,
                self::DanaDikembalikan->value,
            ],
            self::SampaiWhChina->value => [
                self::DikirimKeIndonesia->value,
            ],
            self::DikirimKeIndonesia->value => [
                self::BeaCukai->value,
            ],
            self::BeaCukai->value => [
                self::SampaiWhIndonesia->value,
            ],
            self::SampaiWhIndonesia->value => [
                self::MenungguPelunasanPengambilan->value,
            ],
            self::MenungguPelunasanPengambilan->value => [
                self::SedangDikemas->value,
                self::Hangus->value,
            ],
            self::SedangDikemas->value => [
                self::Selesai->value,
            ],
            self::Hangus->value => [
                self::MenungguDenda->value,
                self::HangusPermanen->value,
            ],
            self::MenungguDenda->value => [
                self::MenungguPelunasanPengambilan->value,
                self::Hangus->value,
                self::HangusPermanen->value,
            ],
        ];
    }

    public function canTransitionTo(self|string $to): bool
    {
        $to = $to instanceof self ? $to->value : $to;

        return in_array($to, self::transitions()[$this->value] ?? [], true);
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
