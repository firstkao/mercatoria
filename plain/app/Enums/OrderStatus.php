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

    /* ============================================================
     * URUTAN & RANK
     * ============================================================ */

    /**
     * Urutan alur pesanan: makin besar = makin maju.
     *
     * Dipakai untuk menghitung status order dari status semua item:
     *   order.status = status item dengan rank TERKECIL (paling mundur).
     *
     * Item yang sudah dibatalkan / dana dikembalikan punya rank 999
     * (di luar progression normal) dan TIDAK dihitung saat menentukan
     * status order — kecuali semua item final, dalam hal itu order
     * ikut jadi dibatalkan / dana dikembalikan.
     */
    public static function rank(string $status): int
    {
        return match ($status) {
            self::MenungguPembayaran->value   => 10,
            self::Ditahan->value              => 15,
            self::PembayaranGagal->value      => 15,
            self::PembayaranDiterima->value   => 20,
            self::SedangDiproses->value       => 30,
            self::SampaiWhCn->value           => 40,
            self::DikirimKeIndonesia->value   => 50,
            self::BeaCukai->value             => 60,
            self::SampaiWhIndonesia->value    => 70,
            self::Selesai->value              => 80,
            self::Dibatalkan->value           => 999,
            self::DanaDikembalikan->value     => 999,
            default                           => 0,
        };
    }

    /**
     * Rank minimum untuk boleh dibatalkan / dana dikembalikan.
     * Di bawah rank ini (belum masuk "sedang_diproses") item boleh dibatalkan.
     */
    public static function cancelLockRank(): int
    {
        return self::rank(self::SedangDiproses->value);
    }

    /**
     * Status final — tidak masuk hitungan min rank, tapi jadi status order
     * kalau SEMUA item berada di salah satu status final ini.
     *
     * @return list<string>
     */
    public static function finalStatuses(): array
    {
        return [
            self::Dibatalkan->value,
            self::DanaDikembalikan->value,
        ];
    }

    /**
     * Status yang uangnya sudah diterima dan belum dikembalikan.
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
     */
    public static function revenueSumSql(string $column = 'pay_now_idr', string $statusColumn = 'status'): string
    {
        $in = implode(',', array_map(static fn (string $v): string => "'".$v."'", self::revenueValues()));

        return "COALESCE(SUM(CASE WHEN {$statusColumn} IN ({$in}) THEN {$column} ELSE 0 END), 0)";
    }

    /* ============================================================
     * TRANSITION MATRIX
     * ============================================================ */

    /**
     * Peta transisi status yang valid.
     * Dipakai untuk validasi backend (canTransition).
     *
     * @return array<string, list<string>>
     */
    public static function transitions(): array
    {
        return [
            self::MenungguPembayaran->value => [
                self::Ditahan->value,
                self::PembayaranDiterima->value,
                self::Dibatalkan->value,
            ],
            self::Ditahan->value => [
                self::MenungguPembayaran->value,
                self::PembayaranDiterima->value,
                self::PembayaranGagal->value,
                self::Dibatalkan->value,
            ],
            self::PembayaranGagal->value => [
                self::MenungguPembayaran->value,
                self::Ditahan->value,
                self::PembayaranDiterima->value,
                self::Dibatalkan->value,
            ],
            self::PembayaranDiterima->value => [
                self::SedangDiproses->value,
                self::Dibatalkan->value,
                self::DanaDikembalikan->value,
            ],
            self::SedangDiproses->value => [
                self::PembayaranDiterima->value,
                self::SampaiWhCn->value,
                self::Dibatalkan->value,
                self::DanaDikembalikan->value,
            ],
            self::SampaiWhCn->value => [
                self::SedangDiproses->value,
                self::DikirimKeIndonesia->value,
                self::DanaDikembalikan->value,
            ],
            self::DikirimKeIndonesia->value => [
                self::SampaiWhCn->value,
                self::BeaCukai->value,
                self::DanaDikembalikan->value,
            ],
            self::BeaCukai->value => [
                self::DikirimKeIndonesia->value,
                self::SampaiWhIndonesia->value,
                self::DanaDikembalikan->value,
            ],
            self::SampaiWhIndonesia->value => [
                self::BeaCukai->value,
                self::Selesai->value,
                self::DanaDikembalikan->value,
            ],
            self::Selesai->value          => [],
            self::Dibatalkan->value       => [],
            self::DanaDikembalikan->value => [],
        ];
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to->value, self::transitions()[$this->value] ?? [], true);
    }

    public static function canTransition(?string $from, ?string $to): bool
    {
        if ($from === null || $to === null) {
            return false;
        }

        $fromCase = self::tryFrom($from);
        $toCase = self::tryFrom($to);

        return $fromCase !== null && $toCase !== null && $fromCase->canTransitionTo($toCase);
    }

    public function isFinal(): bool
    {
        return self::transitions()[$this->value] === [];
    }

    public static function transitionErrorMessage(?string $from, ?string $to): string
    {
        $fromLabel = ($from !== null ? self::tryFrom($from) : null)?->label() ?? ($from ?? '-');
        $toLabel = ($to !== null ? self::tryFrom($to) : null)?->label() ?? ($to ?? '-');

        return "Status pesanan tidak bisa diubah dari \"{$fromLabel}\" ke \"{$toLabel}\".";
    }

    /* ============================================================
     * OPTIONS — UNTUK DROPDOWN ADMIN
     * ============================================================ */

    /**
     * SEMUA status dalam urutan alur logis — untuk dropdown admin.
     *
     * Berbeda dari nextOptionsFor() yang cuma kasih transisi valid.
     * Method ini kasih SEMUA opsi biar admin bebas pilih kalau salah input.
     *
     * Urutan:
     *   1. Menunggu Pembayaran
     *   2. Ditahan
     *   3. Pembayaran Gagal
     *   4. Pembayaran Diterima
     *   5. Sedang Diproses
     *   6. Sampai WH China
     *   7. Dikirim ke Indonesia
     *   8. Bea Cukai
     *   9. Sampai WH Indonesia
     *  10. Selesai
     *  11. Dibatalkan
     *  12. Dana Dikembalikan
     *
     * @return array<string, string>
     */
    public static function allOptionsOrdered(): array
    {
        $ordered = [
            self::MenungguPembayaran,
            self::Ditahan,
            self::PembayaranGagal,
            self::PembayaranDiterima,
            self::SedangDiproses,
            self::SampaiWhCn,
            self::DikirimKeIndonesia,
            self::BeaCukai,
            self::SampaiWhIndonesia,
            self::Selesai,
            self::Dibatalkan,
            self::DanaDikembalikan,
        ];

        $options = [];
        foreach ($ordered as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Opsi dropdown yang VALID dari status saat ini (transisi + current).
     * Kalau kamu mau dropdown dibatasi (bukan semua), pakai method ini.
     *
     * @return array<string, string>
     */
    public static function nextOptionsFor(?string $current): array
    {
        $currentCase = $current !== null ? self::tryFrom($current) : null;

        if ($currentCase === null) {
            return self::allOptionsOrdered();
        }

        $allowed = self::transitions()[$currentCase->value] ?? [];

        $out = [$currentCase->value => $currentCase->label()];
        foreach ($allowed as $value) {
            $out[$value] = self::from($value)->label();
        }

        return $out;
    }

    /**
     * SEMUA opsi (urutan deklarasi enum, bukan alur logis).
     * Biasanya dipakai untuk filter dropdown di index admin.
     *
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

    /* ============================================================
     * LABEL & BADGE
     * ============================================================ */

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran  => 'Menunggu Pembayaran',
            self::Ditahan             => 'Ditahan (Menunggu Verifikasi)',
            self::PembayaranDiterima  => 'Pembayaran Diterima',
            self::SedangDiproses      => 'Sedang Diproses',
            self::SampaiWhCn          => 'Sampai WH China',
            self::DikirimKeIndonesia  => 'Dikirim ke Indonesia',
            self::BeaCukai            => 'Bea Cukai',
            self::SampaiWhIndonesia   => 'Sampai WH Indonesia',
            self::Selesai             => 'Selesai',
            self::Dibatalkan          => 'Dibatalkan',
            self::PembayaranGagal     => 'Pembayaran Gagal',
            self::DanaDikembalikan    => 'Dana Dikembalikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Selesai                                                   => 'badge--on',
            self::Dibatalkan, self::PembayaranGagal, self::DanaDikembalikan => 'badge--danger',
            self::Ditahan, self::BeaCukai                                   => 'badge--warn',
            default                                                         => '',
        };
    }
}
