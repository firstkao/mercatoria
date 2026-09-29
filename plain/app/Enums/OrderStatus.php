<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case Ditahan = 'ditahan';
    case PembayaranDiterima = 'pembayaran_diterima';
    case SedangDiproses = 'sedang_diproses';
    case SampaiWhCn = 'sampai_wh_cn';
    case DikirimKeIndonesia = 'dikirim_ke_indonesia';
    case BeaCukai = 'bea_cukai';
    case SampaiWhIndonesia = 'sampai_wh_indonesia';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
    case PembayaranGagal = 'pembayaran_gagal';
    case DanaDikembalikan = 'dana_dikembalikan';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::Ditahan => 'Ditahan (Menunggu Verifikasi)',
            self::PembayaranDiterima => 'Pembayaran Diterima',
            self::SedangDiproses => 'Sedang Diproses',
            self::SampaiWhCn => 'Sampai WH China',
            self::DikirimKeIndonesia => 'Dikirim ke Indonesia',
            self::BeaCukai => 'Bea Cukai',
            self::SampaiWhIndonesia => 'Sampai WH Indonesia',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::PembayaranGagal => 'Pembayaran Gagal',
            self::DanaDikembalikan => 'Dana Dikembalikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Selesai => 'badge--on',
            self::Dibatalkan, self::PembayaranGagal, self::DanaDikembalikan => 'badge--danger',
            self::Ditahan, self::BeaCukai => 'badge--warn',
            default => '',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}