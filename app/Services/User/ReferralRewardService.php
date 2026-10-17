<?php

namespace App\Services\User;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use Illuminate\Support\Facades\DB;

class ReferralRewardService
{
    public function __construct(
        private readonly CoinWalletService $coins,
        private readonly ScheduledEmailService $emails,
    ) {}

    public function recordRegistration(User $referee): void
    {
        if (! $referee->invited_by_code) {
            return;
        }

        $referrer = User::query()
            ->where('referral_code', $referee->invited_by_code)
            ->where('id', '!=', $referee->id)
            ->first();
        if (! $referrer) {
            return;
        }

        Referral::firstOrCreate(
            ['referrer_id' => $referrer->id, 'referee_id' => $referee->id],
            ['referee_registered_at' => now()],
        );
        UserNotificationService::push(
            $referrer->id,
            NotificationType::REFERRAL,
            'Teman mendaftar melalui kode undanganmu',
            $referee->name.' sudah mendaftar. Bonus koin diberikan setelah syarat transaksi terpenuhi.',
            route('referrals.index'),
            subtype: 'referral_signup',
            meta: ['referee_id' => $referee->id],
        );
    }

    public function onFirstPayment(int $refereeId): void
    {
        $reward = DB::transaction(function () use ($refereeId): ?array {
            $referral = Referral::query()->where('referee_id', $refereeId)->lockForUpdate()->first();
            if (! $referral || $referral->referee_first_paid_at) {
                return null;
            }

            $referee = User::query()->findOrFail($refereeId);
            $awarded = $this->coins->award(
                $referee,
                1000,
                CoinSource::REFEREE_REWARD,
                "Bonus referee pembayaran pertama #{$referral->id}",
            );
            $referral->update([
                'referee_first_paid_at' => now(),
                'referee_coin_given' => $awarded ? 1000 : $referral->referee_coin_given,
            ]);

            return $awarded ? ['user' => $referee, 'amount' => 1000, 'template' => 'referee_reward'] : null;
        });

        if ($reward) {
            $this->notifyReward($reward['user'], $reward['amount'], $reward['template'], 'Pembayaran pertama terverifikasi');
        }
    }

    public function onOrderCompleted(Order $order): void
    {
        $rewards = DB::transaction(function () use ($order): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $buyer = User::query()->findOrFail($lockedOrder->user_id);
            $referral = Referral::query()->where('referee_id', $buyer->id)->lockForUpdate()->first();
            $buyerCoins = (int) floor((float) $lockedOrder->total / 100);
            $rewards = [];

            if ($buyerCoins > 0 && $this->coins->award(
                $buyer,
                $buyerCoins,
                CoinSource::ORDER_COMPLETED,
                "Bonus order selesai {$lockedOrder->order_no}",
            )) {
                $rewards[] = ['user' => $buyer, 'amount' => $buyerCoins, 'template' => 'coin_earned', 'reason' => 'Pesanan selesai'];
            }

            if ($referral && ! $referral->referrer_first_order_completed_at) {
                $referrer = User::query()->findOrFail($referral->referrer_id);
                $awarded = $this->coins->award(
                    $referrer,
                    3000,
                    CoinSource::REFERRER_REWARD,
                    "Bonus referrer order pertama #{$referral->id}",
                );
                $referral->update([
                    'referee_first_order_completed_at' => now(),
                    'referrer_coin_given' => $awarded ? 3000 : $referral->referrer_coin_given,
                ]);
                if ($awarded) {
                    $rewards[] = ['user' => $referrer, 'amount' => 3000, 'template' => 'referral_reward', 'reason' => 'Order pertama teman yang kamu undang selesai'];
                }
            }

            return $rewards;
        });

        foreach ($rewards as $reward) {
            $this->notifyReward($reward['user'], $reward['amount'], $reward['template'], $reward['reason']);
        }
    }

    private function notifyReward(User $user, int $amount, string $template, string $reason): void
    {
        UserNotificationService::push(
            $user->id,
            NotificationType::COIN,
            'Koin bertambah',
            "{$amount} koin ditambahkan: {$reason}.",
            route('wallet.index'),
            subtype: 'coin_earned',
            meta: ['amount' => $amount, 'source' => $template],
        );
        $this->emails->send($user, $template, 'Koin bertambah', [
            'nama_user' => $user->name,
            'jumlah_koin' => number_format($amount, 0, ',', '.'),
            'link' => route('wallet.index'),
            'tanggal' => now()->format('d-m-Y'),
        ]);
    }
}
