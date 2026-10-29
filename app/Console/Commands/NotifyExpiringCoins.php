<?php

namespace App\Console\Commands;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;

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
    protected $description = 'Notifikasi H-30 koin akan expired (PRD 9.3)';

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

        $this->info(count($expiring).' user diperingatkan H-30 expiry koin.');

        return self::SUCCESS;
    }
}
