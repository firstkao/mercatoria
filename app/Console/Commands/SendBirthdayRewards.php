<?php

namespace App\Console\Commands;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\User\CoinWalletService;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;

class SendBirthdayRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:birthday-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bonus ulang tahun: 5.000 koin + voucher (PRD 9.2 / 9.6)';

    public function handle(CoinWalletService $coins, UserNotificationService $notifications): int
    {
        $today = now();

        $users = User::query()
            ->whereNotNull('tgl_lahir')
            ->whereMonth('tgl_lahir', $today->month)
            ->whereDay('tgl_lahir', $today->day)
            ->get();

        foreach ($users as $user) {
            $alreadyRewarded = CoinTransaction::query()
                ->where('user_id', $user->id)
                ->where('type', CoinTransaction::TYPE_EARNED_BIRTHDAY)
                ->whereDate('created_at', $today->toDateString())
                ->exists();

            if ($alreadyRewarded) {
                continue;
            }

            $coins->credit(
                $user,
                (int) setting('coin_birthday_bonus', 5000),
                null,
                CoinTransaction::TYPE_EARNED_BIRTHDAY,
                'Bonus ulang tahun'
            );

            $voucher = Voucher::firstOrCreate(
                ['code' => 'BDAY-'.$today->format('Ymd').'-'.$user->id],
                [
                    'name' => 'Voucher Ulang Tahun',
                    'type' => 'nominal',
                    'nominal' => (float) setting('voucher_birthday_nominal', 25000),
                    'min_belanja' => (float) setting('voucher_birthday_min_belanja', 100000),
                    'is_active' => true,
                ]
            );

            UserVoucher::firstOrCreate(
                ['user_id' => $user->id, 'voucher_id' => $voucher->id],
                ['status' => UserVoucher::STATUS_ACTIVE, 'expires_at' => now()->addDays(30)]
            );

            $notifications->notify($user, 'voucher', 'Voucher ulang tahun diterima', '/profile/wallet');
        }

        $this->info(count($users).' user berulang tahun diproses.');

        return self::SUCCESS;
    }
}
