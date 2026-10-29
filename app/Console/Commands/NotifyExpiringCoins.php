<?php

namespace App\Console\Commands;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserVoucher;
use App\Notifications\VoucherExpiringNotification;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyExpiringCoins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin:expiry-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifikasi H-30 koin & voucher akan expired (PRD 9.3)';

    public function handle(UserNotificationService $notifications): int
    {
        $types = [
            CoinTransaction::TYPE_EARNED_ORDER,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFERRER,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFEREE,
            CoinTransaction::TYPE_EARNED_SHARE,
            CoinTransaction::TYPE_EARNED_BIRTHDAY,
        ];

        $expiring = CoinTransaction::query()
            ->whereIn('type', $types)
            ->where('amount', '>', 0)
            ->whereBetween('expired_at', [now(), now()->addDays(30)])
            ->get()
            ->groupBy('user_id');

        foreach ($expiring as $userId => $lots) {
            $alreadyNotified = UserNotification::where('user_id', $userId)
                ->where('type', 'coin_expiring')
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $notifications->notify(
                User::findOrFail($userId),
                'coin_expiring',
                'Koin sejumlah '.$lots->sum('amount').' akan kedaluwarsa dalam 30 hari',
                '/profile/koin'
            );
        }

        $voucherCount = $this->notifyExpiringVouchers($notifications);

        $this->info(count($expiring).' user diperingatkan H-30 expiry koin, '.$voucherCount.' voucher diperingatkan.');

        return self::SUCCESS;
    }

    /**
     * PRD 9.3: voucher aktif yang akan expired (H-30) -> bell + email.
     */
    private function notifyExpiringVouchers(UserNotificationService $notifications): int
    {
        $expiringVouchers = UserVoucher::query()
            ->where('status', UserVoucher::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->get()
            ->groupBy('user_id');

        $count = 0;

        foreach ($expiringVouchers as $userId => $vouchers) {
            $alreadyNotified = UserNotification::where('user_id', $userId)
                ->where('type', 'voucher_expiring')
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $user = User::findOrFail($userId);
            $tanggal = $vouchers->min('expires_at')->format('d/m/Y');

            $notifications->notify(
                $user,
                'voucher_expiring',
                'Voucher kamu akan expired pada '.$tanggal,
                '/profile/wallet'
            );
            Notification::send($user, new VoucherExpiringNotification($tanggal));
            $count++;
        }

        return $count;
    }
}
