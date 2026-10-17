<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    case DITAHAN = 'ditahan';
    case PEMBAYARAN_DITERIMA = 'pembayaran_diterima';
    case SEDANG_DIPROSES = 'sedang_diproses';
    case SAMPAI_WH_CHINA = 'sampai_wh_china';
    case DIKIRIM_KE_INDONESIA = 'dikirim_ke_indonesia';
    case BEA_CUKAI = 'bea_cukai';
    case SAMPAI_WH_INDONESIA = 'sampai_wh_indonesia';
    case MENUNGGU_PELUNASAN = 'menunggu_pelunasan';
    case SEDANG_DIKEMAS = 'sedang_dikemas';
    case SELESAI = 'selesai';

    case PEMBAYARAN_GAGAL = 'pembayaran_gagal';
    case DANA_DIKEMBALIKAN = 'dana_dikembalikan';
    case HANGUS = 'hangus';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
            self::DITAHAN => 'Ditahan (Verifikasi)',
            self::PEMBAYARAN_DITERIMA => 'Pembayaran Diterima',
            self::SEDANG_DIPROSES => 'Sedang Diproses',
            self::SAMPAI_WH_CHINA => 'Sampai WH China',
            self::DIKIRIM_KE_INDONESIA => 'Dikirim ke Indonesia',
            self::BEA_CUKAI => 'Bea Cukai',
            self::SAMPAI_WH_INDONESIA => 'Sampai WH Indonesia',
            self::MENUNGGU_PELUNASAN => 'Menunggu Pelunasan & Pengambilan',
            self::SEDANG_DIKEMAS => 'Sedang Dikemas',
            self::SELESAI => 'Selesai',
            self::PEMBAYARAN_GAGAL => 'Pembayaran Gagal',
            self::DANA_DIKEMBALIKAN => 'Dana Dikembalikan',
            self::HANGUS => 'Hangus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => '#FFF9C4',
            self::DITAHAN => '#FFE0B2',
            self::PEMBAYARAN_DITERIMA => '#C8E6C9',
            self::SEDANG_DIPROSES, self::SAMPAI_WH_CHINA, self::DIKIRIM_KE_INDONESIA,
            self::SAMPAI_WH_INDONESIA, self::SEDANG_DIKEMAS => '#B3E5FC',
            self::BEA_CUKAI => '#FFE0B2',
            self::MENUNGGU_PELUNASAN => '#FFCC80',
            self::SELESAI => '#C8E6C9',
            self::PEMBAYARAN_GAGAL, self::DANA_DIKEMBALIKAN => '#FFCDD2',
            self::HANGUS => '#CFD8DC',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => '⏳',
            self::DITAHAN => '🔍',
            self::PEMBAYARAN_DITERIMA => '✅',
            self::SEDANG_DIPROSES => '⚙️',
            self::SAMPAI_WH_CHINA => '🏭',
            self::DIKIRIM_KE_INDONESIA => '✈️',
            self::BEA_CUKAI => '🛃',
            self::SAMPAI_WH_INDONESIA => '📦',
            self::MENUNGGU_PELUNASAN => '⏰',
            self::SEDANG_DIKEMAS => '📦',
            self::SELESAI => '🎉',
            self::PEMBAYARAN_GAGAL => '❌',
            self::DANA_DIKEMBALIKAN => '💸',
            self::HANGUS => '🚫',
        };
    }

    public static function activeStatuses(): array
    {
        return [
            self::MENUNGGU_PEMBAYARAN, self::DITAHAN, self::PEMBAYARAN_DITERIMA,
            self::SEDANG_DIPROSES, self::SAMPAI_WH_CHINA, self::DIKIRIM_KE_INDONESIA,
            self::BEA_CUKAI, self::SAMPAI_WH_INDONESIA, self::MENUNGGU_PELUNASAN,
            self::SEDANG_DIKEMAS,
        ];
    }

    public static function endedStatuses(): array
    {
        return [
            self::SELESAI, self::PEMBAYARAN_GAGAL, self::DANA_DIKEMBALIKAN, self::HANGUS,
        ];
    }

    public function isActive(): bool
    {
        return in_array($this, self::activeStatuses());
    }

    public function isEnded(): bool
    {
        return in_array($this, self::endedStatuses());
    }
}
