<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Spammer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReblockExpiredSpammers extends Command
{
    protected $signature = 'spammers:recheck-grace';

    protected $description = 'Reblock spammer entries whose seven-day grace period expired without a purchase';

    public function handle(): int
    {
        $expired = Spammer::query()
            ->where('status', 'watchlist')
            ->whereNotNull('unblocked_until')
            ->where('unblocked_until', '<=', now())
            ->orderBy('id')
            ->get();

        foreach ($expired as $spammer) {
            DB::transaction(function () use ($spammer): void {
                $locked = Spammer::query()->lockForUpdate()->find($spammer->id);

                if (! $locked || $locked->status !== 'watchlist' || $locked->unblocked_until?->isFuture()) {
                    return;
                }

                $becameCustomer = Customer::query()
                    ->whereHas('user', fn (Builder $query) => $query
                        ->where(function (Builder $query) use ($locked): void {
                            if ($locked->email) {
                                $query->where('email', $locked->email);
                            }
                            if ($locked->no_hp) {
                                $method = $locked->email ? 'orWhere' : 'where';
                                $query->{$method}('no_hp', $locked->no_hp);
                            }
                        }))
                    ->exists();

                $locked->update([
                    'status' => $becameCustomer ? 'watchlist' : 'blocked',
                    'unblocked_until' => null,
                ]);
                $locked->logs()->create([
                    'action' => $becameCustomer ? 'grace_completed' : 'grace_expired',
                    'note' => $becameCustomer
                        ? 'Customer bertransaksi selama masa grace period.'
                        : 'Masa grace period berakhir tanpa transaksi.',
                ]);
            });
        }

        $this->info("Checked {$expired->count()} expired spammer grace period(s).");

        return self::SUCCESS;
    }
}
