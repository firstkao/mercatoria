<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\CoinTransaction;
use Illuminate\Console\Command;

class ExpireCoins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Koin expired 1 tahun sejak didapat dihapus dari saldo (PRD 9.3)';

    public function handle(): int
    {
        $types = [
            CoinTransaction::TYPE_EARNED_ORDER,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFERRER,
            CoinTransaction::TYPE_EARNED_REFERRAL_REFEREE,
            CoinTransaction::TYPE_EARNED_SHARE,
            CoinTransaction::TYPE_EARNED_BIRTHDAY,
        ];

        $lots = CoinTransaction::query()
            ->whereIn('type', $types)
            ->where('amount', '>', 0)
            ->where('expired_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($lots as $lot) {
            // Idempoten: baris expired dengan note "lot:{id}" sudah pernah memproses lot ini.
            $already = CoinTransaction::where('type', CoinTransaction::TYPE_EXPIRED)
                ->where('note', 'lot:'.$lot->id)
                ->exists();

            if ($already) {
                continue;
            }

            $coin = Coin::where('user_id', $lot->user_id)->lockForUpdate()->first();

            if ($coin) {
                $before = $coin->balance;
                $deduct = min($lot->amount, $before);
                $coin->decrement('balance', $deduct);

                CoinTransaction::create([
                    'user_id' => $lot->user_id,
                    'order_id' => $lot->order_id,
                    'type' => CoinTransaction::TYPE_EXPIRED,
                    'amount' => -$deduct,
                    'balance_after' => $coin->fresh()->balance,
                    'note' => 'lot:'.$lot->id,
                ]);
            }

            $count++;
        }

        $this->info("{$count} lot koin expired diproses.");

        return self::SUCCESS;
    }
}
