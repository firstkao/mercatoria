<?php

namespace App\Console\Commands;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyExpiringCoins extends Command
{
    protected $signature = 'coin:expiry-notify';

    protected $description = 'Send idempotent H-30, H-7, and H-1 coin expiry notifications';

    public function handle(ScheduledEmailService $emails): int
    {
        $this->expireDueCoins();
        $sent = 0;
        foreach ([30, 7, 1] as $days) {
            $expiryDate = now()->addDays($days);
            CoinTransaction::query()
                ->with('user')
                ->where('status', 'active')
                ->where('amount', '>', 0)
                ->whereBetween('expires_at', [$expiryDate->copy()->startOfDay(), $expiryDate->copy()->endOfDay()])
                ->orderBy('id')
                ->chunkById(100, function ($transactions) use ($days, $emails, &$sent): void {
                    foreach ($transactions as $transaction) {
                        $user = $transaction->user;
                        if (! $user) {
                            continue;
                        }
                        $subtype = "coin_expiring_{$days}";
                        if ($user->notifications()
                            ->where('subtype', $subtype)
                            ->whereJsonContains('meta->coin_transaction_id', $transaction->id)
                            ->exists()) {
                            continue;
                        }

                        $message = number_format($transaction->amount).' koin akan kedaluwarsa dalam '.$days.' hari.';
                        UserNotificationService::push(
                            $user->id,
                            NotificationType::COIN,
                            'Koin akan kedaluwarsa',
                            $message,
                            route('dashboard'),
                            subtype: $subtype,
                            meta: ['coin_transaction_id' => $transaction->id, 'days_remaining' => $days],
                        );
                        $emails->send($user, 'coin_expiring', 'Koin akan kedaluwarsa', [
                            'nama_user' => $user->name,
                            'jumlah_koin' => number_format($transaction->amount),
                            'masa' => $days.' hari',
                            'link' => route('dashboard'),
                            'tanggal' => now()->format('d-m-Y'),
                        ]);
                        $sent++;
                    }
                });
        }

        $this->info("Sent {$sent} coin expiry notification(s).");

        return self::SUCCESS;
    }

    private function expireDueCoins(): void
    {
        CoinTransaction::query()
            ->where('status', 'active')
            ->where('amount', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->chunkById(100, function ($transactions): void {
                foreach ($transactions as $transaction) {
                    DB::transaction(function () use ($transaction): void {
                        $dueTransactions = CoinTransaction::query()
                            ->where('user_id', $transaction->user_id)
                            ->where('status', 'active')
                            ->where('amount', '>', 0)
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', now())
                            ->lockForUpdate()
                            ->get();

                        if ($dueTransactions->isEmpty()) {
                            return;
                        }

                        $coin = Coin::query()
                            ->where('user_id', $transaction->user_id)
                            ->lockForUpdate()
                            ->first();
                        $expiredAmount = $dueTransactions->sum('amount');
                        $deduction = min($expiredAmount, $coin?->balance ?? 0);

                        $dueTransactions->each(fn (CoinTransaction $due) => $due->update(['status' => 'expired']));

                        if ($deduction > 0 && $coin) {
                            $coin->decrement('balance', $deduction);
                            $coin->refresh();
                            CoinTransaction::create([
                                'user_id' => $coin->user_id,
                                'amount' => -$deduction,
                                'source' => CoinSource::EXPIRED->value,
                                'description' => 'Koin kedaluwarsa.',
                                'balance_after' => $coin->balance,
                                'status' => 'expired',
                            ]);
                        }
                    });
                }
            }, 'user_id');
    }
}
