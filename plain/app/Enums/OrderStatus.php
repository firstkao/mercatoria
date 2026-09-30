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

    /**
     * Status yang uangnya sudah diterima dan belum dikembalikan. Hanya order dengan status
     * ini yang dihitung sebagai pendapatan (bukan menunggu_pembayaran / ditahan / dibatalkan /
     * pembayaran_gagal / dana_dikembalikan).
     *
     * @return list<string>
     */
    public static function revenueValues(): array
    {
        return [
            self::PembayaranDiterima->value,
            self::SedangDiproses->value,
            self::SampaiWhCn->value,
            self::DikirimKeIndonesia->value,
            self::BeaCukai->value,
            self::SampaiWhIndonesia->value,
            self::Selesai->value,
        ];
    }

    /**
     * Ekspresi SQL: jumlahkan kolom uang hanya untuk order berstatus pendapatan.
     * Aman di-inline karena nilainya konstanta enum, bukan input user.
     */
    public static function revenueSumSql(string $column = 'pay_now_idr', string $statusColumn = 'status'): string
    {
        $in = implode(',', array_map(static fn (string $v): string => "'".$v."'", self::revenueValues()));

        return "COALESCE(SUM(CASE WHEN {$statusColumn} IN ({$in}) THEN {$column} ELSE 0 END), 0)";
    }

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
