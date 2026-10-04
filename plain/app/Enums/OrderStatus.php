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

    /**
     * Matriks transisi status yang DIIZINKAN.
     *
     * Aturan utamanya: order yang uangnya sudah masuk (revenueValues()) TIDAK BOLEH
     * dimundurkan lagi ke status "belum bayar" (menunggu_pembayaran / pembayaran_gagal /
     * ditahan). Dulu admin bisa menggeser order `selesai` balik ke `menunggu_pembayaran`
     * hanya dengan memilih di dropdown — order tiba-tiba keluar dari omzet, lalu 24 jam
     * kemudian dibatalkan otomatis oleh CancelUnpaidOrders.
     *
     * Selain itu, status akhir (selesai / dibatalkan / dana_dikembalikan) bersifat final
     * supaya koin cashback & refund tidak bisa dipicu dua kali.
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
            // Status akhir: tidak ada transisi keluar.
            self::Selesai->value => [],
            self::Dibatalkan->value => [],
            self::DanaDikembalikan->value => [],
        ];
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to->value, self::transitions()[$this->value] ?? [], true);
    }

    /**
     * Aman dipanggil dengan string mentah (mis. dari DB / request yang belum divalidasi).
     */
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

    /**
     * Pilihan dropdown untuk admin: status sekarang + status yang boleh dituju.
     * Dipakai supaya UI tidak pernah menawarkan transisi yang bakal ditolak controller.
     *
     * @return array<string, string>
     */
    public static function nextOptionsFor(?string $current): array
    {
        $currentCase = $current !== null ? self::tryFrom($current) : null;

        if ($currentCase === null) {
            return self::options();
        }

        $allowed = self::transitions()[$currentCase->value] ?? [];

        $out = [$currentCase->value => $currentCase->label()];
        foreach ($allowed as $value) {
            $out[$value] = self::from($value)->label();
        }

        return $out;
    }

    /**
     * Pesan error yang ramah untuk admin.
     */
    public static function transitionErrorMessage(?string $from, ?string $to): string
    {
        $fromLabel = ($from !== null ? self::tryFrom($from) : null)?->label() ?? ($from ?? '-');
        $toLabel = ($to !== null ? self::tryFrom($to) : null)?->label() ?? ($to ?? '-');

        return "Status pesanan tidak bisa diubah dari \"{$fromLabel}\" ke \"{$toLabel}\".";
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
