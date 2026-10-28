<?php

namespace App\Services\User;

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CoinWalletService
{
    /**
     * Potong saldo koin untuk order (PRD 9.3: langsung terpotong saat "Buat Pesanan").
     * Wajib dipanggil di dalam DB::transaction — lockForUpdate menjamin
     * dua checkout serentak tidak memakai saldo yang sama.
     */
    public function debitForOrder(User $user, int $amount, Order $order): CoinTransaction
    {
        $coin = Coin::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if (! $coin || $coin->balance < $amount) {
            throw ValidationException::withMessages([
                'coin_amount' => 'Saldo koin tidak cukup.',
            ]);
        }

        $coin->decrement('balance', $amount);

        return CoinTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => CoinTransaction::TYPE_USED_AT_CHECKOUT,
            'amount' => -$amount,
            'balance_after' => $coin->fresh()->balance,
        ]);
    }
}
