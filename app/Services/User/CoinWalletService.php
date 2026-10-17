<?php

namespace App\Services\User;

use App\Enums\CoinSource;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CoinWalletService
{
    public function award(User $user, int $amount, CoinSource $source, string $description): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Coin awards must be a positive whole number.');
        }

        return DB::transaction(function () use ($user, $amount, $source, $description): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            if (CoinTransaction::query()
                ->where('user_id', $lockedUser->id)
                ->where('source', $source->value)
                ->where('description', $description)
                ->exists()) {
                return false;
            }

            $coin = Coin::query()->firstOrCreate(['user_id' => $lockedUser->id], ['balance' => 0]);
            $coin = Coin::query()->lockForUpdate()->findOrFail($coin->id);
            $coin->increment('balance', $amount);
            $coin->refresh();

            CoinTransaction::create([
                'user_id' => $lockedUser->id,
                'amount' => $amount,
                'source' => $source->value,
                'description' => $description,
                'balance_after' => $coin->balance,
                'expires_at' => now()->addYear(),
                'status' => 'active',
            ]);

            return true;
        });
    }
}
