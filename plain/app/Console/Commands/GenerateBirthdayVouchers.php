<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateBirthdayVouchers extends Command
{
    protected $signature = 'vouchers:birthday {--force : Paksa generate meskipun sudah ada}';
    protected $description = 'Generate voucher ulang tahun untuk customer yang berulang tahun hari ini';

    public function handle(): int
    {
        $today = now('Asia/Jakarta');
        $year = $today->year;

        // Setting default
        $nominal = Setting::integer('birthday_voucher_nominal', 20000);
        $minPurchase = Setting::integer('birthday_voucher_min', 100000);
        $validDays = Setting::integer('birthday_voucher_valid_days', 3);

        // Customer yang ultah hari ini
        $birthdayCustomers = User::query()
            ->where('role', 'customer')
            ->whereNotNull('email')       // skip user yang sudah dianonimkan
            ->whereNull('anonymized_at')  // skip user yang sudah dihapus datanya
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->get();

        $generated = 0;
        $skipped = 0;

        foreach ($birthdayCustomers as $user) {
            // Skip kalau sudah dapat voucher tahun ini
            $exists = Voucher::query()
                ->where('user_id', $user->id)
                ->where('auto_type', 'birthday')
                ->where('auto_year', $year)
                ->exists();

            if ($exists && ! $this->option('force')) {
                $skipped++;
                continue;
            }

            // Generate kode unik: HBD{year}-{slug nama}-{random4}
            $nameSlug = Str::upper(Str::slug(Str::limit((string) $user->full_name, 12, ''), ''));
            if ($nameSlug === '') {
                $nameSlug = 'USER' . $user->id;
            }
            $code = "HBD{$year}-{$nameSlug}-" . Str::upper(Str::random(4));

            $voucher = Voucher::create([
                'user_id' => $user->id,
                'code' => $code,
                'name' => "Voucher Ulang Tahun {$year}",
                'discount_type' => 'nominal',
                'value' => $nominal,
                'max_discount_idr' => null,
                'min_purchase_idr' => $minPurchase,
                'is_birthday' => true,
                'is_personal' => true,
                'auto_type' => 'birthday',
                'auto_year' => $year,
                // Berlaku 3 hari sebelum → 3 hari setelah ultah
                'starts_at' => $today->copy()->subDays(3)->startOfDay(),
                'ends_at' => $today->copy()->addDays($validDays)->endOfDay(),
                'usage_limit' => 1,
                'per_user_limit' => 1,
                'is_active' => true,
            ]);

            // Notifikasi ke user (opsional — jangan sampai ganggu loop)
            try {
                NotificationService::send(
                    $user,
                    'voucher',
                    '🎂 Selamat Ulang Tahun!',
                    "Kamu dapat voucher diskon Rp" . number_format($nominal, 0, ',', '.') .
                    " (min. belanja Rp" . number_format($minPurchase, 0, ',', '.') . "). Kode: {$code}. Berlaku sampai " .
                    $voucher->ends_at->timezone('Asia/Jakarta')->translatedFormat('j F Y') . ".",
                    null,
                    'wallet',
                );
            } catch (\Throwable $e) {
                // ignore — voucher tetap dibuat walau notif gagal
            }

            $generated++;
        }

        $this->info("Birthday vouchers: {$generated} dibuat, {$skipped} dilewati.");

        return self::SUCCESS;
    }
}