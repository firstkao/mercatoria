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

    /**
     * Tambah saldo koin (PRD 9.2). lockForUpdate + saldo setelah transaksi
     * dihitung dari row yang sama — dua kredit serentak tidak korup.
     *
     * expired_at = 1 tahun sejak didapat (PRD 9.3).
     */
    public function credit(User $user, int $amount, ?Order $order, string $type, ?string $note = null): CoinTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Nominal koin harus positif.');
        }

        $coin = Coin::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if (! $coin) {
            $coin = Coin::create(['user_id' => $user->id, 'balance' => 0]);
        }

        $coin->increment('balance', $amount);

        return CoinTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order?->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $coin->fresh()->balance,
            'expired_at' => now()->addYear(),
            'note' => $note,
        ]);
    }
}
