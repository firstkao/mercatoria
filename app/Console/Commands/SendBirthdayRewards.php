<?php

namespace App\Console\Commands;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendBirthdayRewards extends Command
{
    protected $signature = 'user:birthday-bonus';

    protected $description = 'Grant annual birthday coins and a 30-day nominal voucher to customers';

    public function handle(ScheduledEmailService $emails): int
    {
        $granted = 0;
        User::query()
            ->whereNotNull('tgl_lahir')
            ->whereMonth('tgl_lahir', now()->month)
            ->whereDay('tgl_lahir', now()->day)
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($emails, &$granted): void {
                foreach ($users as $user) {
                    $voucher = DB::transaction(function () use ($user): ?Voucher {
                        $lockedUser = User::query()->lockForUpdate()->find($user->id);
                        if (! $lockedUser || CoinTransaction::query()
                            ->where('user_id', $lockedUser->id)
                            ->where('source', CoinSource::BIRTHDAY->value)
                            ->whereYear('created_at', now()->year)
                            ->exists()) {
                            return null;
                        }

                        $coin = Coin::query()->firstOrCreate(['user_id' => $lockedUser->id], ['balance' => 0]);
                        $coin = Coin::query()->lockForUpdate()->findOrFail($coin->id);
                        $coin->increment('balance', 5000);
                        $coin->refresh();
                        CoinTransaction::create([
                            'user_id' => $lockedUser->id,
                            'amount' => 5000,
                            'source' => CoinSource::BIRTHDAY->value,
                            'description' => 'Bonus ulang tahun tahunan.',
                            'balance_after' => $coin->balance,
                            'expires_at' => now()->addYear(),
                            'status' => 'active',
                        ]);

                        $voucher = Voucher::create([
                            'code' => 'BDAY-'.now()->format('Y').'-'.Str::upper(Str::random(10)),
                            'name' => 'Voucher Ulang Tahun',
                            'type' => 'nominal',
                            'value' => 25000,
                            'min_purchase' => 100000,
                            'valid_from' => now(),
                            'valid_until' => now()->addDays(30),
                            'per_user_limit' => 1,
                            'is_active' => true,
                        ]);
                        UserVoucher::create([
                            'user_id' => $lockedUser->id,
                            'voucher_id' => $voucher->id,
                            'status' => 'active',
                        ]);
                        UserNotificationService::push(
                            $lockedUser->id,
                            NotificationType::BIRTHDAY,
                            'Selamat ulang tahun!',
                            '5.000 koin dan voucher Rp25.000 sudah ditambahkan. Voucher berlaku 30 hari dengan minimum belanja Rp100.000.',
                            route('dashboard'),
                            subtype: 'birthday_reward',
                            meta: ['voucher_id' => $voucher->id],
                        );

                        return $voucher;
                    });

                    if (! $voucher) {
                        continue;
                    }

                    $emails->send($user, 'birthday_reward', 'Selamat ulang tahun dari Mercatoria', [
                        'nama_user' => $user->name,
                        'jumlah_koin' => '5.000',
                        'voucher' => $voucher->code,
                        'link' => route('dashboard'),
                        'tanggal' => now()->format('d-m-Y'),
                    ]);
                    $granted++;
                }
            });

        $this->info("Granted birthday rewards to {$granted} customer(s).");

        return self::SUCCESS;
    }
}
